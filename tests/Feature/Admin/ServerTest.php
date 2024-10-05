<?php

namespace Tests\Feature\Admin;

use App\Models\Account\Customer;
use App\Models\Provisioning\Server;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerTest extends TestCase
{

    const API_URL = 'admin/servers';
    use RefreshDatabase;

    public function test_admin_server_index(): void
    {
        $this->seed(AdminSeeder::class);
        $admin = \App\Models\Admin\Admin::first();
        $response = $this->actingAs($admin, 'admin')->get(self::API_URL);
        $response->assertStatus(200);
    }

    public function test_admin_server_get(): void
    {
        $this->seed(AdminSeeder::class);
        $id = Server::create([
            'name' => 'Test Server',
            'address' => 'test.com',
            'hostname' => 'test.com',
            'status' => 'active',
            'username' => "XXXX",
            'password' => "XXXX",
            'type' => 'pterodactyl',
            'port' => 443,
        ])->id;
        $admin = \App\Models\Admin\Admin::first();
        $response = $this->actingAs($admin, 'admin')->get(self::API_URL .'/' . $id);
        $response->assertStatus(200);
    }

    public function test_admin_server_update(): void
    {
        $this->seed(AdminSeeder::class);
        $id = Server::create([
            'name' => 'Test Server',
            'address' => 'test.com',
            'hostname' => 'test.com',
            'status' => 'active',
            'username' => "XXXX",
            'password' => "XXXX",
            'type' => 'pterodactyl',
            'port' => 443,
        ])->id;
        $admin = \App\Models\Admin\Admin::first();
        $response = $this->actingAs($admin, 'admin')->put(self::API_URL . '/' . $id, [
            'name' => 'Test Server',
            'address' => 'test2.com',
            'hostname' => 'test2.com',
            'status' => 'active',
            'type' => 'pterodactyl',
            'username' => "XXXX",
            'password' => "XXXX",
            'port' => 443,
        ]);
        $response->assertStatus(302);

    }

    public function test_admin_server_create(): void
    {
        $this->seed(AdminSeeder::class);
        $admin = \App\Models\Admin\Admin::first();
        $response = $this->actingAs($admin, 'admin')->post(self::API_URL, [
            'name' => 'Test Server',
            'address' => 'test3.com',
            'hostname' => 'test3.com',
            'status' => 'active',
            'type' => 'pterodactyl',

            'username' => "XXXX",
            'password' => "XXXX",
            'port' => 443,
        ]);
        $response->assertStatus(302);
    }

}
