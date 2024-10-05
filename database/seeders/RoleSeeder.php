<?php

namespace Database\Seeders;

use App\Models\Core\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = file_get_contents(resource_path('roles.json'));
        $roles = json_decode($roles, true);
        if (Role::count() > 0) {
            return;
        }
        foreach ($roles as $role) {
            $permissions = $role['permissions'];
            $tmp = [];
            unset($role['permissions']);
            $role = \App\Models\Core\Role::updateOrCreate($role);
            foreach ($permissions as $permission) {
                $permission = \App\Models\Core\Permission::where('name', $permission)->first();
                if ($permission)
                    $tmp[] = $permission->id;
            }
            $role->permissions()->sync($tmp);
        }
    }
}
