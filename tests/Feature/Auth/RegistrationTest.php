<?php

namespace Tests\Feature\Auth;

use App\Helpers\Countries;
use App\Models\Admin\Setting;
use App\Services\SettingsService;
use Database\Seeders\EmailTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_can_register(): void
    {
        $this->seed(EmailTemplateSeeder::class);
        $response = $this->post('/register', [
            'firstname' => 'Test User',
            'lastname' => 'Test User',
            'zipcode' => '59100',
            'region' => 'Test User',
            'country' => 'FR',
            'email' => 'test@example.com',
            'address' => 'test',
            'city' => 'test',
            'phone' => '0123456789',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/client');
    }


    public function test_show_register_form(): void
    {
        $response = $this->get('/login');
        $response->assertOk();
    }

    public function test_new_users_cannot_register_because_zipcode(): void
    {
        $response = $this->post('/register', [
            'firstname' => 'Test User',
            'lastname' => 'Test User',
            'zipcode' => 'Test User',
            'region' => 'Test User',
            'country' => 'FR',
            'email' => 'test@example.com',
            'address' => 'test',
            'city' => 'test',
            'phone' => '0123456789',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response->assertSessionHasErrors(['zipcode']);
        $this->assertGuest();
    }

    public function test_new_users_can_register_because_tos_accepted(): void
    {
        $this->seed(EmailTemplateSeeder::class);

        app(SettingsService::class)->set('register_toslink', "https://example.com/tos");
        $response = $this->post('/register', [
            'firstname' => 'Test User',
            'lastname' => 'Test User',
            'zipcode' => '59100',
            'region' => 'Test User',
            'country' => 'FR',
            'email' => 'test@example.com',
            'address' => 'test',
            'city' => 'test',
            'phone' => '0123456789',
            'accept_tos' => 'on',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $this->assertAuthenticated();
    }

    public function test_new_users_cannot_register_because_tos_not_accepted(): void
    {
        Setting::updateSettings(['register_toslink' => "https://example.com/tos"]);
        $response = $this->post('/register', [
            'firstname' => 'Test User',
            'lastname' => 'Test User',
            'zipcode' => '59100',
            'region' => 'Test User',
            'country' => 'FR',
            'email' => 'test@example.com',
            'address' => 'test',
            'city' => 'test',
            'phone' => '0123456789',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response->assertSessionHasErrors(['accept_tos']);
        $this->assertGuest();
    }
}
