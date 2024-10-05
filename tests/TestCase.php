<?php

namespace Tests;

use App\Models\Account\Customer;
use App\Models\Admin\Admin;
use App\Models\Core\Gateway;
use App\Models\Store\Group;
use App\Models\Store\Pricing;
use App\Models\Store\Product;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;


    protected function performAction(string $method, string $url, array $abbilities = ['*'], array $data = []): TestResponse
    {
        $this->seed(AdminSeeder::class);
        $token = Admin::first()->createToken('test-admin', $abbilities);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token->plainTextToken, 'Accept' => 'application/json'])->json($method, $url, $data);
        Admin::first()->tokens()->delete();
        return $response;
    }
    protected function createGroupModel(string $status = 'active')
    {
        $group = new Group();
        $group->name = 'Test Group';
        $group->slug = 'test-slug';
        $group->description = 'Test Group Description';
        $group->status = $status;
        $group->id = 1;
        $group->save();
        return $group;
    }

    protected function createGatewayModel()
    {
        $gateway = new Gateway();
        $gateway->name = 'Test Gateway';
        $gateway->uuid = 'balance';
        $gateway->status = 'active';
        $gateway->id = 1;
        $gateway->save();
        return $gateway;
    }

    protected function createProductModel(string $status = 'active', int $stock = 1)
    {
        if (!Group::find(1)){
            $this->createGroupModel();
        }
        $product = new Product();
        $product->name = 'Test Product';
        $product->status = $status;
        $product->description = 'Test Product Description';
        $product->sort_order = 1;
        $product->group_id = 1;
        $product->stock = $stock;
        $product->type = 'none';
        $product->save();
        $this->createPriceModel($product->id, 'USD');
        return $product;
    }

    protected function createPriceModel(int $product_id, string $currency = 'USD', array $prices = ['monthly' => 1])
    {
        $price = new Pricing();
        $price->related_id = $product_id;
        $price->related_type = 'product';
        $price->currency = $currency;
        $price->onetime = $prices['onetime'] ?? null;
        $price->monthly = $prices['monthly'];
        $price->quarterly = $prices['quarterly'] ?? null;
        $price->semiannually = $prices['semiannually'] ?? null;
        $price->annually = $prices['annually'] ?? null;
        $price->biennially = $prices['biennially'] ?? null;
        $price->triennially = $prices['triennially'] ?? null;
        $price->save();
        return $price;
    }

    protected function createCustomerModel()
    {
        return Customer::factory()->create();
    }

    protected function createServiceModel(int $customer_id, string $status = 'active')
    {
        $service = new \App\Models\Provisioning\Service();
        $service->name = 'Test Service';
        $service->type = 'none';
        $service->status = $status;
        $service->expires_at = \Carbon\Carbon::now()->addDays(30);
        $service->price = 10.00;
        $service->customer_id = $customer_id;
        $service->save();
        return $service;
    }


}
