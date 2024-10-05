<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Admin\AbstractCrudController;
use App\Models\Core\Permission;
use App\Models\Core\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\RequiredIf;

class RoleController extends AbstractCrudController
{
    protected string $model = Role::class;
    protected string $viewPath = 'admin.core.roles';
    protected string $routePath = 'admin.roles';
    protected string $translatePrefix = 'admin.roles';
    protected ?string $managedPermission = 'admin.manage_roles';


    public function getCreateParams(): array
    {
        $userRole = auth('admin')->user()->role;
        $params = parent::getCreateParams();
        $params['permissions'] = $this->permissions();
        $params['item']->level = $userRole->level;
        return $params;
    }

    public function show(Role $role)
    {
        $this->checkPermission('show');
        $params['item'] = $role;
        $params['permissions'] = $this->permissions();
        return $this->showView($params);
    }


    public function store(Request $request)
    {
        $userRole = auth('admin')->user()->role;
        $validated = $request->validate([
            'name' => 'required|max:200',
            'level' => 'required|integer|max:' . $userRole->level,
            'is_admin' => 'nullable',
            'is_default' => 'nullable',
            'permissions' => ['array', new RequiredIf($request->get('is_admin') == 'false')],
        ]);
        $this->checkPermission('create');
        $role = new Role();

        if ($validated['level'] > $userRole->level) {
            return back()->with('error', __('admin.roles.error_update'));
        }
        if ($request->get('is_default') && $userRole->is_admin) {
            Role::where('is_default', true)->where('id', '!=', $role->id)->update(['is_default' => false]);
            $role->is_default = true;
        }
        if ($userRole->level < $validated['level']) {
            return back()->with('error', __('admin.roles.error_update_level'));
        }
        $role->name = $validated['name'];
        if ($request->get('is_admin') && $userRole->is_admin) {
            $role->is_admin = true;
            $permissions = [];
        } else {
            $role->is_admin = false;
            $permissions = $validated['permissions'];
        }
        $role->save();
        $role->permissions()->sync($permissions);
        return $this->storeRedirect($role);
    }

    public function update(Role $role, Request $request)
    {
        $userRole = auth('admin')->user()->role;
        $validated = $request->validate([
            'name' => 'required|max:200',
            'level' => ['required', 'integer', 'min:' . $role->level, 'max:' . $userRole->level],
            'is_admin' => 'nullable',
            'is_default' => 'nullable',
            'permissions' => ['array', new RequiredIf($request->get('is_admin') == 'false')],
        ]);
        $this->checkPermission('update');
        if ($role->level > $userRole->level && $role->id != $userRole->id) {
            return back()->with('error', __('admin.roles.error_update'));
        }

        if ($request->get('is_default') && $userRole->is_admin) {
            Role::where('is_default', true)->where('id', '!=', $role->id)->update(['is_default' => false]);
            $role->is_default = true;
        }
        if ($userRole->level < $role->level) {
            return back()->with('error', __('admin.roles.error_update_level'));
        }
        $role->name = $request->name;
        $role->level = $request->level;
        if ($request->get('is_admin') && $userRole->is_admin) {
            $role->is_admin = true;
            $role->permissions()->sync([]);
        } else {
            $role->is_admin = false;
            $role->permissions()->sync($request->permissions);
        }
        $role->update();
        return $this->updateRedirect($role);
    }

    public function destroy(Role $role)
    {
        $this->checkPermission('delete');
        if ($role->default || $role->level >= auth('admin')->user()->role->level) {
            return back()->with('error', __('admin.roles.error_delete'));
        }
        $role->permissions()->detach();
        $default = Role::where('default', true)->first();
        $role->staffs->each(function ($staff) use ($role, $default) {
            $staff->update(['role_id' => $default->id]);
        });
        $role->delete();
        return $this->deleteRedirect($role);
    }

    private function permissions()
    {
        $permissions = Permission::all();
        $tmp = [];
        foreach ($permissions as $permission) {
            if (!isset($tmp[$permission->group]))
                $tmp[$permission->group] = [];
            $tmp[$permission->group][] = $permission;
        }
        uasort($tmp, [$this, 'compareBySubArrayLength']);
        return $tmp;
    }

    public function compareBySubArrayLength($a, $b) {
        $countA = count($a);
        $countB = count($b);

        if ($countA == $countB) {
            return 0;
        }

        return ($countA > $countB) ? -1 : 1;
    }
}
