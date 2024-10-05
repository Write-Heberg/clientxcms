<?php

namespace Tests\Feature\Auth;

use App\Models\Account\Customer;
use Database\Seeders\EmailTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = Customer::factory()->create();
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $this->assertAuthenticated();
        $response->assertRedirect();
    }

    public function test_show_login_form(): void
    {
        $response = $this->get('/login');
        $response->assertOk();
    }


    public function test_show_forgot_form(): void
    {
        $response = $this->get('/forgot-password');
        $response->assertOk();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = Customer::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = Customer::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect();
    }


    public function test_users_can_resend_verify_email(): void
    {
        $user = Customer::create([
            'firstname' => fake()->firstName(),
            'lastname' => fake()->lastName(),
            'country' => fake()->countryCode(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'address2' => fake()->secondaryAddress(),
            'city' => fake()->city(),
            'region' => fake()->state(),
            'zipcode' => fake()->postcode(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
        ]);
        $this->seed(EmailTemplateSeeder::class);

        $response = $this->actingAs($user)->get('/client/emails/resend');
        $this->assertAuthenticated();
        $response->assertRedirect();
    }


    public function test_users_cannot_resend_verify_email(): void
    {
        $user = Customer::factory()->create();
        $user->markEmailAsVerified();
        $response = $this->actingAs($user)->get('/client/emails/resend');
        $this->assertAuthenticated();
        $response->assertRedirect();
    }
}
