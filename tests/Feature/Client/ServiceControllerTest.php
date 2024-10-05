<?php

namespace Client;

use App\Models\Account\Customer;
use App\Models\Core\Invoice;
use App\Models\Core\InvoiceItem;
use App\Models\Provisioning\Service;
use App\Models\Provisioning\ServiceRenewals;
use App\Services\Store\TaxesService;
use Database\Seeders\EmailTemplateSeeder;
use Database\Seeders\GatewaySeeder;
use Database\Seeders\ServerSeeder;
use Database\Seeders\StoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ServiceControllerTest extends TestCase
{

    use RefreshDatabase;
    public function test_services_index(): void
    {
        $this->seed(ServerSeeder::class);

        $this->seed(StoreSeeder::class);

        Customer::factory(15)->create();
        Service::factory(15)->create();
        $user = $this->createCustomerModel();
        $this->actingAs($user)->get(route('front.services.index'))->assertOk();
    }


    public function test_services_valid_filter(): void
    {
        $this->seed(ServerSeeder::class);

        $this->seed(StoreSeeder::class);

        Customer::factory(15)->create();
        Service::factory(15)->create();
        $user = $this->createCustomerModel();
        $this->actingAs($user)->get(route('front.services.index') . '?filter=active')->assertOk();
    }


    public function test_services_invalid_filter(): void
    {
        $user = $this->createCustomerModel();
        $this->actingAs($user)->get(route('front.services.index') . '?filter=suuuu')->assertRedirect();
    }


    public function test_services_can_show(): void
    {

        $this->seed(\Database\Seeders\ModuleSeeder::class);
        app('extension')->autoload(app());

        $this->seed(ServerSeeder::class);
        $this->seed(StoreSeeder::class);
        Customer::factory(15)->create();
        /** @var Service $service */
        $service = Service::factory()->create();
        /** @var Customer $user */
        $user = $service->customer;
        $this->actingAs($user)->get(route('front.services.show', ['service' => $service->id]))->assertOk();
    }


    public function test_services_renewals(): void
    {
        $this->seed(ServerSeeder::class);
        $this->seed(StoreSeeder::class);
        Customer::factory(15)->create();
        /** @var Service $service */
        $service = Service::factory()->create();
        /** @var Customer $user */
        $user = $service->customer;

        $this->actingAs($user)->get(route('front.services.renewal', ['service' => $service->id]))->assertOk();
    }
    public function test_services_cannot_show(): void
    {
        $this->seed(ServerSeeder::class);
        $this->seed(StoreSeeder::class);
        Customer::factory(15)->create();
        /** @var Service $service */
        $service = Service::factory()->create();
        /** @var Customer $user */
        $user = Customer::where('id', '!=', $service->customer_id)->first();

        $this->actingAs($user)->get(route('front.services.show', ['service' => $service->id]))->assertNotFound();
    }

    public function test_services_can_renew_because_is_expired(): void
    {
        $this->seed(StoreSeeder::class);
        $this->seed(GatewaySeeder::class);
        $this->seed(ServerSeeder::class);
        Customer::factory(15)->create();
        /** @var service $service */
        $service = Service::factory()->create();
        /** @var Customer $user */
        $user = $service->customer;
        $service->status = 'expired';
        $service->save();
        $this->actingAs($user)->get(route('front.services.renew', ['service' => $service->id, 'gateway' => 'balance']))->assertRedirect(route('front.services.show', ['service' => $service->id]));
    }


    public function test_services_cannot_renew_because_max_renewal_is_attempts(): void
    {
        $this->seed(ServerSeeder::class);

        $this->seed(\Database\Seeders\ModuleSeeder::class);
        app('extension')->autoload(app());

        $this->seed(StoreSeeder::class);
        $this->seed(GatewaySeeder::class);
        Customer::factory(15)->create();
        /** @var service $service */
        $service = Service::factory()->create();
        /** @var Customer $user */
        $user = $service->customer;
        $service->status = 'active';
        $service->renewals = 11;
        $service->max_renewals = 10;
        $service->save();
        $this->actingAs($user)->get(route('front.services.renew', ['service' => $service->id, 'gateway' => 'balance']))->assertRedirect(route('front.services.show', ['service' => $service->id]));
    }


    public function test_services_can_renew(): void
    {
        $this->seed(ServerSeeder::class);

        $this->seed(\Database\Seeders\ModuleSeeder::class);
        app('extension')->autoload(app());

        $this->seed(StoreSeeder::class);
        $this->seed(GatewaySeeder::class);
        $this->seed(EmailTemplateSeeder::class);

        Customer::factory(15)->create();
        /** @var ServiceRenewals $renewal */
        $renewal = ServiceRenewals::factory()->create();
        /** @var Service $service */
        $service = $renewal->service;
        /** @var Invoice $invoice */
        $invoice = $renewal->invoice;
        /** @var Customer $user */
        $user = $service->customer;
        $service->status = 'active';
        $service->renewals = 9;
        $service->max_renewals = 10;
        $service->save();
        $this->actingAs($user)->get(route('front.services.renew', ['service' => $service->id, 'gateway' => 'balance']))->assertRedirect();
        $this->assertDatabaseCount('invoices', 2);
        $this->assertDatabaseCount('invoice_items', 2);
        $this->assertDatabaseCount('service_renewals', 2);
        $this->assertDatabaseCount('email_messages', 1);
        $service = Service::find($service->id);
        $this->assertEquals($service->invoice_id, $invoice->id + 1);
        $invoice = Invoice::find($invoice->id + 1);
        $this->assertEquals($service->price, $invoice->subtotal);
        $tax = TaxesService::getTaxAmount($service->price, tax_percent());
        $total = number_format(TaxesService::getAmount($service->price, tax_percent()) + $tax, 2);
        $this->assertEquals($total, $invoice->total);
        $this->assertEquals('pending', $invoice->status);
    }

    public function beforeRefreshingDatabase()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        InvoiceItem::truncate();
        Invoice::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
