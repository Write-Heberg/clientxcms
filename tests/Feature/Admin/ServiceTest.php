<?php

namespace Tests\Feature\Admin;

use App\Models\Provisioning\Server;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTest extends TestCase
{

    const API_URL = 'admin/services';
    use RefreshDatabase;

    public function test_admin_service_index(): void
    {
        $this->seed(AdminSeeder::class);
        $admin = \App\Models\Admin\Admin::first();
        $response = $this->actingAs($admin, 'admin')->get(self::API_URL);
        $response->assertStatus(200);
    }

    public function test_admin_service_invalid_filter(): void
    {
        $this->seed(AdminSeeder::class);
        $admin = \App\Models\Admin\Admin::first();
        $response = $this->actingAs($admin, 'admin')->get(self::API_URL . '?filter=test');
        $response->assertStatus(302);
    }

    public function test_admin_service_valid_filter(): void
    {
        $this->seed(AdminSeeder::class);
        $admin = \App\Models\Admin\Admin::first();
        $response = $this->actingAs($admin, 'admin')->get(self::API_URL . '?filter=active');
        $response->assertStatus(200);
    }


    public function test_admin_service_search(): void
    {
        $this->seed(AdminSeeder::class);
        $admin = \App\Models\Admin\Admin::first();
        $response = $this->actingAs($admin, 'admin')->get(self::API_URL . '?q=example');
        $response->assertStatus(200);
    }


    public function test_admin_service_get(): void
    {
        $this->seed(AdminSeeder::class);
        $customer = $this->createCustomerModel();
        $id = $this->createServiceModel($customer->id)->id;
        $admin = \App\Models\Admin\Admin::first();
        $response = $this->actingAs($admin, 'admin')->get(self::API_URL .'/' . $id);
        $response->assertStatus(200);
    }

    public function test_admin_service_update(): void
    {

        $this->seed(AdminSeeder::class);
        $this->seed(\Database\Seeders\ServerSeeder::class);
        $customer = $this->createCustomerModel();
        $product = $this->createProductModel();
        $id = $this->createServiceModel($customer->id)->id;
        $admin = \App\Models\Admin\Admin::first();
        $response = $this->actingAs($admin, 'admin')->put(self::API_URL . '/' . $id, [
            'name' => 'test 2',
            'customer_id' => $customer->id,
            'type' => 'none',
            'status' => 'active',
            'price' => '1',
            'currency' => 'USD',
            'product_id' => $product->id,
            'billing' => 'monthly',
            'server_id' => Server::first()->id,
        ]);
        $response->assertStatus(302);
    }

    public function test_admin_service_create_show(): void
    {
        $this->seed(AdminSeeder::class);
        $customer = $this->createCustomerModel();
        $admin = \App\Models\Admin\Admin::first();
        $response = $this->actingAs($admin, 'admin')->get(self::API_URL . '/create');
        $response->assertStatus(200);
    }

    public function test_admin_service_create_part_without_product(): void
    {
        $this->seed(AdminSeeder::class);
        $customer = $this->createCustomerModel();
        $admin = \App\Models\Admin\Admin::first();
        $response = $this->actingAs($admin, 'admin')->get(self::API_URL . '/create?customer_id=' . $customer->id);
        $response->assertStatus(302);
        $response = $this->actingAs($admin, 'admin')->get(self::API_URL . '/create?customer_id=' . $customer->id . '&product_id=none&type=none');
        $response->assertStatus(200);
    }


    public function test_admin_service_create_part_with_product(): void
    {
        $this->seed(AdminSeeder::class);
        $customer = $this->createCustomerModel();
        $product = $this->createProductModel();
        $admin = \App\Models\Admin\Admin::first();
        $response = $this->actingAs($admin, 'admin')->get(self::API_URL . '/create?customer_id=' . $customer->id);
        $response->assertStatus(302);
        $response = $this->actingAs($admin, 'admin')->get(self::API_URL . '/create?customer_id=' . $customer->id . '&product_id='. $product->id . '&type=none');
        $response->assertStatus(200);
    }
}
