<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Core\InvoiceItem;
use App\Models\Provisioning\ServiceRenewals;
use Database\Factories\Provisioning\ServiceRenewalFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->environment('local')) {
            //\App\Models\Account\Customer::factory(30)->create();
            //\App\Models\Store\Group::factory(10)->create();
            //\App\Models\Store\Pricing::factory(20)->create();

            $this->call([
                ServerSeeder::class,
                //AdminSeeder::class,
                ModuleSeeder::class,
                StoreSeeder::class,
            ]);
            //InvoiceItem::factory(30)->create();
        }
        $this->call([
            EmailTemplateSeeder::class,
            CancellationReasonSeeder::class,
            GatewaySeeder::class,
            ThemeSeeder::class,
            SupportDepartmentSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);
    }
}
