<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin;

use App\Helpers\Countries;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Account\Customer;
use App\Models\Account\EmailMessage;
use App\Models\ActionLog;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class CustomerController extends AbstractCrudController
{
    protected string $viewPath = 'admin.core.customers';
    protected string $routePath = 'admin.customers';
    protected string $translatePrefix = 'admin.customers';
    protected string $model = \App\Models\Account\Customer::class;
    protected int $perPage = 25;
    protected string $searchField = 'email';

    public function getCreateParams()
    {
        $data = parent::getCreateParams();
        $data['countries'] = Countries::names();
        return $data;
    }

    public function getIndexFilters()
    {
        return [];
    }

    public function getSearchFields()
    {
        return [
            'id' => 'ID',
            'email' => __('global.email'),
            'firstname' => __('global.firstname'),
            'lastname' => __('global.lastname'),
            'phone' => __('global.phone'),
        ];
    }

    public function show(Customer $customer)
    {
        $this->checkPermission('show', $customer);
        $params['item'] = $customer;
        $params['countries'] = Countries::names();
        $params['invoices'] = $customer->invoices()->paginate(3);
        $params['services'] = $customer->services()->paginate(3);
        $params['emails'] = $customer->emails()->paginate(5);
        $params['tickets'] = $customer->tickets()->paginate(5);
        $params['logs'] = $customer->getLogsAction(ActionLog::NEW_LOGIN)->paginate(10);
        return $this->showView($params);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $this->checkPermission('update', $customer);
        $data = $request->validated();
        if ($request->has('password') && $request->password != '') {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }
        $customer->update($data);
        return $this->updateRedirect($customer);
    }

    public function autologin(Customer $customer)
    {
        $this->checkPermission('admin.autologin_customer', $customer);
        \Session::put('autologin', true);
        \Session::put('autologin_customer', $customer->id);
        auth('web')->loginUsingId($customer->id);
        \Session::flash('success', __('admin.customers.autologin.success', ['name' => $customer->fullName]));
        return redirect()->to(RouteServiceProvider::HOME);
    }

    public function logout()
    {
        $this->checkPermission('admin.autologin_customer');
        auth('web')->logout();
        $customer = Customer::find(\Session::get('autologin_customer'));
        \Session::remove('autologin');
        \Session::remove('autologin_customer');
        \Session::flash('success', __('admin.customers.autologin.logoutsuccess', ['name' => $customer->fullName]));
        return redirect()->route('admin.customers.show', $customer);
    }

    public function destroy(Customer $customer)
    {
        $this->checkPermission('delete', $customer);
        if ($customer->invoices()->count() > 0) {
            \Session::flash('error', __('admin.customers.delete.error'));
            return redirect()->back();
        }
        $customer->delete();
        return $this->deleteRedirect($customer);
    }

    public function store(StoreCustomerRequest $request)
    {
        $this->checkPermission('create');
        $data = $request->validated();
        if (!isset($data['password']) || $data['password'] == '' || $data['password'] == null) {
            $data['password'] = \Str::uuid();
        }
        $data['password'] = Hash::make($data['password']);
        $customer = Customer::create($data);
        if ($request->password == null) {
            Password::broker('users')->sendResetLink($request->only('email'));
        }
        return $this->storeRedirect($customer);
    }

    public function search(Request $request)
    {
        if (in_array('field', array_keys($request->all()))) {
            if (in_array($request->get('field'), ['id', 'email', 'first_name', 'last_name', 'phone'])){
                $this->searchField = $request->get('field');
                if ($request->get('field') == 'id'){
                    $customer = Customer::find($request->get('q'));
                    return collect([$customer]);
                }
            }
            if ($request->get('field') == 'service_id') {
                $service = \App\Models\Provisioning\Service::where('id', (int)$request->get('q'))->first();
                if ($service){
                    $this->routePath = 'admin.services';
                    return collect([$service]);
                }
            }
            if ($request->get('field') == 'invoice_id') {
                $invoice = \App\Models\Core\Invoice::where('id', (int)$request->get('q'))->first();
                if ($invoice){
                    $this->routePath = 'admin.invoices';
                    return collect([$invoice]);
                }
            }
        }
        return parent::search($request);
    }

    public function action(Request $request, Customer $customer, string $action)
    {
        $this->checkPermission('update', $customer);
        switch ($action) {
            case 'suspend':
                $customer->suspend($request->reason ?? 'No reason provided', $request->force ?? false);
                break;
            case 'reactivate':
                $customer->reactivate();
                break;
            case 'ban':
                $customer->ban($request->reason ?? 'No reason provided', $request->force ?? false);
                break;
            default:
                break;
        }
        return redirect()->back()->with('success', __($this->translatePrefix . '.show.action_success'));
    }
}
