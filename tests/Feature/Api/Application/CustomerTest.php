<?php

namespace Api\Application;

use App\Models\Account\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{

    const API_URL = 'api/application/customers';
    const ABILITY_INDEX = 'customers:index';
    const ABILITY_STORE = 'customers:store';
    const ABILITY_SHOW = 'customers:show';
    const ABILITY_UPDATE = 'customers:update';
    const ABILITY_DELETE = 'customers:delete';

    use RefreshDatabase;

    public function test_api_application_customer_index(): void
    {
        $response = $this->performAction('GET', self::API_URL, [self::ABILITY_INDEX]);
        $response->assertStatus(200);
    }

    public function test_api_application_customer_store(): void
    {
        $response = $this->performAction('POST', self::API_URL, [self::ABILITY_STORE], [
            'firstname' => 'Test User',
            'lastname' => 'Test User',
            'zipcode' => '59100',
            'region' => 'Test User',
            'country' => 'FR',
            'email' => 'test@example.com',
            'address' => 'test',
            'city' => 'test',
            'phone' => '0223456789',
            'password' => 'password',
        ]);
        $response->assertStatus(201);
    }

    public function test_api_application_customer_verified_store(): void
    {
        $response = $this->performAction('POST', self::API_URL, [self::ABILITY_STORE], [
            'firstname' => 'Test User',
            'lastname' => 'Test User',
            'zipcode' => '59100',
            'region' => 'Test User',
            'country' => 'FR',
            'email' => 'test@example.com',
            'address' => 'test',
            'city' => 'test',
            'phone' => '0323456789',
            'password' => 'password',
            'verified' => '1',
        ]);
        $response->assertStatus(201);
        $response->assertJsonFragment(['is_confirmed' => true]);
    }

    public function test_api_application_customer_get(): void
    {
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
        $response = $this->performAction('GET', self::API_URL .'/' . $id, [self::ABILITY_SHOW]);
        $response->assertStatus(200);
    }

    public function test_api_application_customer_delete(): void
    {
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
        $response = $this->performAction('DELETE', self::API_URL . '/' . $id, [self::ABILITY_DELETE]);
        $response->assertStatus(200);
    }

    public function test_api_application_customer_update(): void
    {
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
        $response = $this->performAction('POST', self::API_URL . '/' . $id, [self::ABILITY_UPDATE], [
            'email' => 'admin@administration.com',
            'city' => 'roubaix',
            'firstname' => 'Martin',
            'zipcode' => '59100',
        ]);
        $response->assertStatus(200);
        $response->assertJsonFragment(['email' => 'admin@administration.com', 'city' => 'roubaix', 'firstname' => 'Martin']);
    }

}
