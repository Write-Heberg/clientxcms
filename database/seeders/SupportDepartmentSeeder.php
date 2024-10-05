<?php

namespace Database\Seeders;

use App\Models\Helpdesk\SupportDepartment;
use Illuminate\Database\Seeder;

class SupportDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (SupportDepartment::count() !== 0) {
            return;
        }
        $departments = [
            [
                'name' => __('client.support.departmentsseeder.general.name'),
                'description' => __('client.support.departmentsseeder.general.description'),
                'icon' => 'bi bi-question-circle',
            ],
            [
                'name' => __('client.support.departmentsseeder.billing.name'),
                'description' => __('client.support.departmentsseeder.billing.description'),
                'icon' => 'bi bi-credit-card',
            ],
            [
                'name' => __('client.support.departmentsseeder.technical.name'),
                'description' => __('client.support.departmentsseeder.technical.description'),
                'icon' => 'bi bi-tools'
            ],
            [
                'name' => __('client.support.departmentsseeder.sales.name'),
                'description' => __('client.support.departmentsseeder.sales.description'),
                'icon' => 'bi bi-cart'
            ],
        ];
        SupportDepartment::insert($departments);
    }
}
