<?php

namespace Store\Basket;

use App\Models\Account\Customer;
use App\Models\Admin\Setting;
use App\Models\Core\Invoice;
use App\Models\Store\Basket\Basket;
use App\Models\Store\Basket\BasketRow;
use App\Models\Store\Product;
use App\Services\SettingsService;
use Database\Seeders\EmailTemplateSeeder;
use Database\Seeders\GatewaySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_get_guest()
    {
        $this->createBasket();
        $this->get(route('front.store.basket.checkout'))->assertOk();
    }

    public function test_checkout_get_logged()
    {
        $user = $this->createCustomerModel();
        $this->createBasket($user);
        $this->actingAs($user)->get(route('front.store.basket.checkout'))->assertOk();
    }

    public function test_checkout_get_mustbeconfirmed()
    {
        app(SettingsService::class)->set('checkout.customermustbeconfirmed', true);
        $user = $this->createCustomerModel();
        $this->createBasket($user);
        $response = $this->actingAs($user)->get(route('front.store.basket.checkout'));
        $response->assertOk();
    }

    public function test_checkout_post_empty_basket()
    {
        $user = $this->createCustomerModel();
        $request = $this->post(route('front.store.basket.checkout'),  [
            'gateway' => 'stripe',
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'address' => $user->address,
            'address2' => $user->address2,
            'city' => $user->city,
            'zipcode' => $user->zipcode,
            'phone' => $user->phone,
            'region' => $user->region,
            'country' => $user->country,
        ]);

        $request->assertRedirect();
    }
    public function test_checkout_post_guest()
    {
        $request = $this->post(route('front.store.basket.checkout'),  [
            'gateway' => 'stripe',
        ]);
        $request->assertRedirect();
        $this->assertGuest();
    }
    public function test_checkout_post_logged_and_unconfirmed()
    {
        $user = $this->createCustomerModel();
        app(SettingsService::class)->set('checkout.customermustbeconfirmed', true);

        $request = $this->post(route('front.store.basket.checkout'),  [
            'gateway' => 'stripe',
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'address' => $user->address,
            'address2' => $user->address2,
            'city' => $user->city,
            'zipcode' => $user->zipcode,
            'phone' => '123456789',
            'region' => $user->region,
            'country' => $user->country,
        ]);
        $this->createBasket();
        $request->assertRedirect();
    }


    public function test_checkout_change_customer_details()
    {
        $this->seed(GatewaySeeder::class);
        $this->seed(EmailTemplateSeeder::class);
        $user = $this->createCustomerModel();
        $this->createBasket($user);
        $user->markEmailAsVerified();
        app(SettingsService::class)->set('checkout.toslink', "https://example.com/tos");
        $request = $this->actingAs($user)->post(route('front.store.basket.checkout'), [
            'gateway' => 'stripe',
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'address' => $user->address,
            'address2' => $user->address2,
            'city' => 'Roubaix',
            'zipcode' => $user->zipcode,
            'phone' => $user->phone,
            'region' => $user->region,
            'accept_tos' => 'on',
            'country' => $user->country,
        ]);
        $request->assertRedirect();
        $this->assertEquals('Roubaix', $user->fresh()->city);
    }
    public function test_checkout_cannot_pay_because_tos_not_accepted()
    {
        $user = $this->createCustomerModel();
        $this->createBasket($user);
        $user->markEmailAsVerified();
        Setting::updateSettings(['checkout.toslink' => "https://example.com/tos"]);
        $request = $this->actingAs($user)->post(route('front.store.basket.checkout'), [
            'gateway' => 'stripe',
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'address' => $user->address,
            'address2' => $user->address2,
            'city' => $user->city,
            'zipcode' => "59100",
            'phone' => $user->phone,
            'region' => $user->region,
            'country' => "FR",
        ]);
        $request->assertRedirect();
        $request->assertSessionHasErrors('accept_tos');
    }

    public function test_checkout_create_invoice()
    {
        $this->seed(GatewaySeeder::class);
        $this->seed(EmailTemplateSeeder::class);
        $user = $this->createCustomerModel();
        $this->createBasket($user);
        $user->markEmailAsVerified();
        Setting::updateSettings(['checkout.toslink' => "https://example.com/tos"]);
        $request = $this->actingAs($user)->post(route('front.store.basket.checkout'), [
            'gateway' => 'stripe',
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'address' => $user->address,
            'address2' => $user->address2,
            'city' => $user->city,
            'zipcode' => $user->zipcode,
            'phone' => $user->phone,
            'region' => $user->region,
            'accept_tos' => 'on',
            'country' => $user->country,
        ]);
        $request->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'customer_id' => $user->id,
            'status' => 'pending',
        ]);
        $invoice = Invoice::orderBy('id', 'DESC')->first();
        $this->assertEquals(1.20, $invoice->total);
        $this->assertEquals(1, $invoice->subtotal);
        $this->assertEquals(0.20, $invoice->tax);
        $this->assertEquals(0, $invoice->setupfees);
        $this->assertEquals('eur', $invoice->currency);
        $this->assertEquals('pending', $invoice->status);
        $item = $invoice->items->first();
        $this->assertEquals('Test Product', $item->name);
        $this->assertEquals('Created from basket item', $item->description);
        $this->assertEquals(1, $item->quantity);
        $this->assertEquals(1, $item->unit_price);
        $this->assertEquals(0, $item->unit_setupfees);
        $this->assertEquals(0, $item->setupfee);
        $this->assertEquals(Product::orderBy('id', 'DESC')->first()->id, $item->related_id);
        $this->assertEquals([], $item->data);
    }

    protected function createBasket(?Customer $customer=null)
    {
        $basket = Basket::create([
            'user_id' => $customer ? $customer->id : null,
            'ipaddress' => request()->ip(),
            'uuid' => Uuid::uuid4(),
        ]);
        BasketRow::create([
            'basket_id' => $basket->id,
            'product_id' => $this->createProductModel()->id,
            'quantity' => 1,
            'billing' => 'monthly',
        ]);
    }
}
