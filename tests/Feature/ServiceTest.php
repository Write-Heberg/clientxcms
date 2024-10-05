<?php

namespace Tests\Feature;

use App\Models\Account\Customer;
use App\Models\Core\Invoice;
use App\Models\Provisioning\Service;
use App\Models\Provisioning\ServiceRenewals;
use App\Services\Store\TaxesService;
use Carbon\Carbon;
use Database\Seeders\EmailTemplateSeeder;
use Database\Seeders\GatewaySeeder;
use Database\Seeders\ServerSeeder;
use Database\Seeders\StoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;
    public function test_renew_service_if_active()
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
        $invoice->items[0]->type = 'renewal';
        $invoice->items[0]->related_id = $service->id;
        $invoice->items[0]->data = [
            'months' => 3,
        ];
        // 3 initial + 3 supplémentaires
        $now = Carbon::now()->addMonths(6);
        $invoice->items[0]->save();
        $service->status = 'active';
        $service->renewals = 1;
        $service->max_renewals = 10;
        $service->save();
        $invoice->complete();
        $service = $service->fresh();
        $this->assertEquals($service->status, 'active');
        $this->assertEquals($service->max_renewals, 10);
        $this->assertEquals($service->renewals, 2);
        $this->assertEquals($now->format('d/m/y'), $service->expires_at->format('d/m/y'));
    }
}
