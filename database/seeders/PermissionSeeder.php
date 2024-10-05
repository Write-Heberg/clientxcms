<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = file_get_contents(resource_path('permissions.json'));
        $permissions = json_decode($permissions, true);
        foreach ($permissions as $permission) {

            \App\Models\Core\Permission::updateOrCreate($permission);
        }
    }
}
