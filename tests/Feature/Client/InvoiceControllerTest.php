<?php

namespace Client;

use App\Models\Account\Customer;
use App\Models\Core\Invoice;
use App\Models\Core\InvoiceItem;
use App\Models\Store\Product;
use Database\Factories\Provisioning\ProductFactory;
use Database\Seeders\EmailTemplateSeeder;
use Database\Seeders\GatewaySeeder;
use Database\Seeders\StoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_invoices_index(): void
    {
        $this->seed(StoreSeeder::class);
        Customer::factory(15)->create();
        InvoiceItem::factory(15)->create();
        $user = $this->createCustomerModel();
        $this->actingAs($user)->get(route('front.invoices.index'))->assertOk();
    }


    public function test_invoices_valid_filter(): void
    {
        $this->seed(StoreSeeder::class);

        Customer::factory(15)->create();
        InvoiceItem::factory(15)->create();

        $user = $this->createCustomerModel();
        $this->actingAs($user)->get(route('front.invoices.index') . '?filter=paid')->assertOk();
    }


    public function test_invoices_invalid_filter(): void
    {
        $this->seed(StoreSeeder::class);
        Customer::factory(15)->create();
        InvoiceItem::factory(15)->create();
        $user = $this->createCustomerModel();
        $this->actingAs($user)->get(route('front.invoices.index') . '?filter=suuuu')->assertRedirect();
    }


    public function test_invoices_can_show(): void
    {
        $this->seed(StoreSeeder::class);
        Customer::factory(15)->create();
        /** @var InvoiceItem $invoiceItem */
        $invoiceItem = InvoiceItem::factory()->create();
        /** @var Invoice $invoice */
        $invoice = $invoiceItem->invoice;
        /** @var Customer $user */
        $user = $invoice->customer;
        $this->actingAs($user)->get(route('front.invoices.show', ['invoice' => $invoice->id]))->assertOk();
    }

    public function test_invoices_can_download(): void
    {
        $this->seed(StoreSeeder::class);
        Customer::factory(15)->create();
        /** @var InvoiceItem $invoiceItem */
        $invoiceItem = InvoiceItem::factory()->create();
        /** @var Invoice $invoice */
        $invoice = $invoiceItem->invoice;
        /** @var Customer $user */
        $user = $invoice->customer;
        $this->actingAs($user)->get(route('front.invoices.download', ['invoice' => $invoice->id]))->assertOk();
    }


    public function test_invoices_cannot_download(): void
    {
        $this->seed(StoreSeeder::class);
        Customer::factory(15)->create();
        /** @var InvoiceItem $invoiceItem */
        $invoiceItem = InvoiceItem::factory()->create();
        /** @var Invoice $invoice */
        $invoice = $invoiceItem->invoice;
        $user = Customer::where('id', '!=', $invoice->customer_id)->first();
        $this->actingAs($user)->get(route('front.invoices.download', ['invoice' => $invoice->id]))->assertNotFound();
    }


    public function test_invoices_can_pay(): void
    {
        $this->seed(EmailTemplateSeeder::class);
        $this->seed(StoreSeeder::class);
        $this->seed(GatewaySeeder::class);
        Customer::factory(15)->create();
        /** @var InvoiceItem $invoiceItem */
        $invoiceItem = InvoiceItem::factory()->create();
        /** @var Invoice $invoice */
        $invoice = $invoiceItem->invoice;
        /** @var Customer $user */
        $user = $invoice->customer;
        $invoice->status = 'pending';
        $invoice->save();
        $this->actingAs($user)->get(route('front.invoices.pay', ['invoice' => $invoice->id, 'gateway' => 'balance']))->assertRedirect();
    }


    public function test_invoices_cannot_pay(): void
    {
        $this->seed(EmailTemplateSeeder::class);

        $this->seed(StoreSeeder::class);
        $this->seed(GatewaySeeder::class);
        Customer::factory(15)->create();
        /** @var InvoiceItem $invoiceItem */
        $invoiceItem = InvoiceItem::factory()->create();
        /** @var Invoice $invoice */
        $invoice = $invoiceItem->invoice;
        /** @var Customer $user */
        $user = $invoice->customer;
        $invoice->status = 'completed';
        $invoice->save();
        $this->actingAs($user)->get(route('front.invoices.pay', ['invoice' => $invoice->id, 'gateway' => 'balance']))->assertRedirect(route('front.invoices.show', ['invoice' => $invoice->id]));
    }

    public function test_invoices_cannot_show(): void
    {
        $this->seed(EmailTemplateSeeder::class);

        $this->seed(StoreSeeder::class);
        Customer::factory(15)->create();
        /** @var InvoiceItem $invoiceItem */
        $invoiceItem = InvoiceItem::factory()->create();
        /** @var Invoice $invoice */
        $invoice = $invoiceItem->invoice;
        /** @var Customer $user */
        $user = Customer::where('id', '!=', $invoice->customer_id)->first();
        $this->actingAs($user)->get(route('front.invoices.show', ['invoice' => $invoice->id]))->assertNotFound();
    }
}
