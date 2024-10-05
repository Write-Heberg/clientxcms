<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin\Helpdesk\Support;

use App\Http\Controllers\Admin\AbstractCrudController;
use App\Models\Helpdesk\SupportDepartment;
use Illuminate\Http\Request;

class DepartmentController extends AbstractCrudController
{
    protected string $viewPath = 'admin.helpdesk.departments';
    protected string $routePath = 'admin.helpdesk.departments';
    protected string $model = SupportDepartment::class;
    protected ?string $managedPermission = 'admin.manage_departments';

    public function store(Request $request)
    {
        $this->checkPermission('create');
        $this->validate($request, [
            'name' => 'required',
            'description' => 'required',
            'icon' => 'required',
        ]);
        $department = SupportDepartment::create($request->all());
        return $this->storeRedirect($department);
    }

    public function show(SupportDepartment $department)
    {
        $this->checkPermission('show');
        return $this->showView(['item' => $department]);
    }

    public function update(Request $request, SupportDepartment $department)
    {
        $this->checkPermission('update');
        $this->validate($request, [
            'name' => 'required',
            'description' => 'required',
            'icon' => 'required',
        ]);
        $department->update($request->all());
        return $this->updateRedirect($department);
    }

    public function destroy(SupportDepartment $department)
    {
        $this->checkPermission('delete');
        $department->delete();
        return $this->deleteRedirect($department);
    }

    protected function getPermissions(string $tablename)
    {
        $tablename = 'helpdesk_departments';
        return parent::getPermissions($tablename);
    }

}
