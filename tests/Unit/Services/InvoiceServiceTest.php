<?php

namespace Tests\Unit\Services;

use App\Models\Account\Customer;
use App\Models\Account\EmailMessage;
use App\Models\Core\Gateway;
use App\Models\Core\Invoice;
use App\Models\Core\InvoiceItem;
use App\Models\Provisioning\ServiceRenewals;
use App\Models\Store\Basket\Basket;
use App\Models\Store\Basket\BasketRow;
use App\Services\Core\InvoiceService;
use Database\Seeders\EmailTemplateSeeder;
use Database\Seeders\StoreSeeder;
use Faker\Provider\Uuid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{

    use RefreshDatabase;
    public function test_create_service_on_invoice_completion()
    {
        $this->seed(EmailTemplateSeeder::class);

        Customer::factory(20)->create();
        $this->createProductModel();

        $this->seed(\Database\Seeders\ModuleSeeder::class);
        app('extension')->autoload(app());
        \Artisan::call('migrate');

        $this->seed(StoreSeeder::class);
        /** @var InvoiceItem $invoiceItem */
        $invoiceItem = InvoiceItem::factory()->create();
        /** @var Invoice $invoice */
        $invoice = $invoiceItem->invoice;
        $invoice->complete();
        $this->assertDatabaseCount('services', 1);
        $this->assertDatabaseCount('email_messages', 1);
        $email = EmailMessage::first();

        $this->assertEquals($email->recipient, $invoice->customer->email);
        $this->assertEquals($email->recipient_id, $invoice->customer_id);
        $this->assertDatabaseCount('service_renewals', 1);
        $this->assertEquals(true, ServiceRenewals::first()->first_period);

    }

    public function test_create_invoice_from_basket()
    {
        $user = $this->createCustomerModel();
        $product = $this->createProductModel();
        $this->seed(EmailTemplateSeeder::class);

        $basket = Basket::create([
            'user_id' => $user->id,
            'ipaddress' => request()->ip(),
            'completed_at' => '2021-01-01 00:00:01',
            'uuid' => Uuid::uuid(),
        ]);
        BasketRow::insert([
            'basket_id' => $basket->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'billing' => 'monthly',
            'currency' => 'USD',
            'options' => '{}',
            'data' => '{}',
        ]);
        $gateway = $this->createGatewayModel();
        $invoice = InvoiceService::createInvoiceFromBasket($basket, $gateway);
        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseCount('invoice_items', 1);
        $this->assertEquals($invoice->total, $basket->total());
        $this->assertEquals($invoice->subtotal, $basket->subtotal());
        $this->assertEquals($invoice->tax, $basket->tax());
        $this->assertEquals($invoice->setupfees, $basket->setup());
        $this->assertEquals($invoice->currency, $basket->items->first()->currency);
        $this->assertEquals($invoice->status, 'pending');
        $this->assertEquals($invoice->external_id, $basket->external_id);
        $this->assertEquals($invoice->notes, "Created from basket #{$basket->id}");
        $this->assertDatabaseCount('email_messages', 1);
        $email = EmailMessage::first();
        $this->assertEquals($email->recipient, $user->email);
        $this->assertEquals($email->recipient_id, $user->id);
    }

    public function beforeRefreshingDatabase()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        InvoiceItem::truncate();
        EmailMessage::truncate();
        ServiceRenewals::truncate();
        Invoice::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

}
