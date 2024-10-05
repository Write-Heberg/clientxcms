<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_dashboard()
    {
        $this->seed(\Database\Seeders\AdminSeeder::class);
        $response = $this->actingAs(\App\Models\Admin\Admin::first(), 'admin')->get('/admin/dashboard');
        $response->assertStatus(200);
    }
}
