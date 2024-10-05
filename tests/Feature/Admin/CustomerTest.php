<?php

namespace Tests\Feature\Admin;

use App\Models\Account\Customer;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{

    const API_URL = 'admin/customers';
    use RefreshDatabase;

    public function test_admin_customer_index(): void
    {
        $this->seed(AdminSeeder::class);
        $admin = \App\Models\Admin\Admin::first();
        $response = $this->actingAs($admin, 'admin')->get(self::API_URL);
        $response->assertStatus(200);
    }


    public function test_admin_customer_invalid_filter(): void
    {
        $this->seed(AdminSeeder::class);
        $admin = \App\Models\Admin\Admin::first();
        $response = $this->actingAs($admin, 'admin')->get(self::API_URL . '?filter=test');
        $response->assertStatus(302);
    }

    public function test_admin_customer_valid_filter(): void
    {
        $this->seed(AdminSeeder::class);
        $admin = \App\Models\Admin\Admin::first();
        $response = $this->actingAs($admin, 'admin')->get(self::API_URL . '?filter=unpaid');
        $response->assertStatus(302);
    }


    public function test_admin_customer_search(): void
    {
        $this->seed(AdminSeeder::class);
        $admin = \App\Models\Admin\Admin::first();
        $response = $this->actingAs($admin, 'admin')->get(self::API_URL . '?q=example');
        $response->assertStatus(200);
    }


    public function test_admin_customer_get(): void
    {
        $this->seed(AdminSeeder::class);
        $id = Customer::create([
            'firstname' => 'Test User',
            'lastname' => 'Test User',
            'zipcode' => '59100',
            'region' => 'Test User',
            'country' => 'FR',
            'email' => 'admin@admin.com',
            'address' => 'test',
            'city' => 'test',
            'phone' => '0323456789',
            'password' => 'password'
        ])->id;
        $admin = \App\Models\Admin\Admin::first();
        $response = $this->actingAs($admin, 'admin')->get(self::API_URL .'/' . $id);
        $response->assertStatus(200);
    }

    public function test_admin_customer_update(): void
    {
        $this->seed(AdminSeeder::class);
        $id = Customer::create([
            'firstname' => 'Test User',
            'lastname' => 'Test User',
            'zipcode' => '59100',
            'region' => 'Test User',
            'country' => 'FR',
            'email' => 'admin@admin.com',
            'address' => 'test',
            'city' => 'test',
            'phone' => '0323456789',
            'password' => 'password'
        ])->id;
        $admin = \App\Models\Admin\Admin::first();
        $response = $this->actingAs($admin, 'admin')->put(self::API_URL . '/' . $id, [
            'firstname' => 'Martin',
            'lastname' => 'Test User',
            'zipcode' => '59100',
            'region' => 'Test User',
            'country' => 'FR',
            'city' => 'roubaix',
            'phone' => '0323456710',
            'id' => $id,
            'email' => 'admin@administration.com',
        ]);
        $response->assertStatus(302);
    }
}
