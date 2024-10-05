<?php

namespace Client;

use App\Models\Helpdesk\SupportDepartment;
use App\Models\Helpdesk\SupportTicket;
use Database\Seeders\SupportDepartmentSeeder;
use Tests\TestCase;

class SupportControllerTest extends TestCase
{
    public function test_client_support_index(): void
    {
        $user = $this->createCustomerModel();
        $ticket = $this->createTicketModel();
        $this->actingAs($user)->get(route('front.support'))->assertOk();
    }

    public function test_client_support_create(): void
    {
        $user = $this->createCustomerModel();
        $this->actingAs($user)->get(route('front.support.create'))->assertOk();
    }

    public function test_client_support_valid_store(): void
    {
        $user = $this->createCustomerModel();
        $department = $this->createDepartmentModel();
        $this->actingAs($user)->post(route('front.support.create'), [
            'department_id' => $department->id,
            'subject' => 'Test Subject',
            'content' => 'Test content',
        ])->assertRedirect();
    }

    public function test_client_support_invalid_store(): void
    {
        $user = $this->createCustomerModel();
        $this->actingAs($user)->post(route('front.support.create'), [
            'department_id' => 30,
            'subject' => 'Test Subject',
            'content' => '',
        ])->assertSessionHasErrors();
    }

    public function test_client_support_invalid_related_type_store(): void
    {
        $user = $this->createCustomerModel();
        $this->actingAs($user)->post(route('front.support.create'), [
            'department_id' => 1,
            'subject' => 'Test Subject',
            'content' => 'Test content',
            'priority' => 'low',
            'related_id' => '1-test'
        ])->assertSessionHasErrors();
    }

    public function test_client_support_invalid_related_id_store(): void
    {
        $user = $this->createCustomerModel();
        $this->actingAs($user)->post(route('front.support.create'), [
            'department_id' => 1,
            'subject' => 'Test Subject',
            'content' => 'Test content',
            'priority' => 'low',
            'related_id' => 'service-30'
        ])->assertSessionHasErrors();
    }

    private function createTicketModel()
    {
        $this->seed(SupportDepartmentSeeder::class);
        return SupportTicket::factory()->create();
    }

    private function createDepartmentModel()
    {
        return SupportDepartment::factory()->create();
    }
}
