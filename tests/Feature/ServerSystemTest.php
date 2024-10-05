<?php

namespace Tests\Feature;

use App\Models\Provisioning\Server;
use Database\Seeders\ServerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ServerSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_decrypt_password(): void
    {
        $this->seed(ServerSeeder::class);
        $server = Server::first();
        $this->assertEquals(env('PTERODACTYL_API_KEY'), $server->password);
    }

}
