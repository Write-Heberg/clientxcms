<?php

namespace Client;

use App\Models\Account\Customer;
use App\Models\Account\EmailMessage;
use App\Models\Admin\EmailTemplate;
use App\Models\Core\Invoice;
use App\Models\Core\InvoiceItem;
use App\Models\Store\Product;
use Database\Factories\Provisioning\ProductFactory;
use Database\Seeders\EmailTemplateSeeder;
use Database\Seeders\GatewaySeeder;
use Database\Seeders\StoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_emails_index(): void
    {
        $this->seed(EmailTemplateSeeder::class);
        $this->seed(StoreSeeder::class);
        Customer::factory(15)->create();
        EmailMessage::factory(15)->create();
        $user = $this->createCustomerModel();
        $this->actingAs($user)->get(route('front.emails.index'))->assertOk();
    }

    public function test_emails_search(): void
    {
        $this->seed(EmailTemplateSeeder::class);

        $this->seed(StoreSeeder::class);
        Customer::factory(15)->create();
        EmailMessage::factory(15)->create();
        $user = $this->createCustomerModel();
        $this->actingAs($user)->get(route('front.emails.index') . '?search=notification')->assertOk();
    }

    public function test_email_can_show(): void
    {
        $this->seed(EmailTemplateSeeder::class);

        Customer::factory(15)->create();
        $this->seed(EmailTemplateSeeder::class);

        $email = EmailMessage::create([
            'recipient_id' => Customer::first()->id,
            'subject' => 'test',
            'content' => 'test',
            'recipient' => 'test@clientxcms.com',
            'template' => EmailTemplate::first()->id,
        ]);
        /** @var Customer $user */
        $user = Customer::first();
        $this->actingAs($user)->get(route('front.emails.show', ['email' => $email->id]))->assertOk();
    }


    public function test_invoices_cannot_download(): void
    {
        Customer::factory(15)->create();
        $this->seed(EmailTemplateSeeder::class);
        $email = EmailMessage::create([
            'recipient_id' => Customer::first()->id,
            'subject' => 'test',
            'content' => 'test',
            'recipient' => 'test@clientxcms.com',
            'template' => EmailTemplate::first()->id,
        ]);
        $user = Customer::where('id', '!=', $email->recipient_id)->first();
        $this->actingAs($user)->get(route('front.emails.show', ['email' => $email->id]))->assertNotFound();
    }

}
