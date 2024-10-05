<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin\Provisioning;

use App\DTO\Admin\MassActionDTO;
use App\DTO\Provisioning\ProvisioningTabDTO;
use App\DTO\Provisioning\ServiceStateChangeDTO;
use App\DTO\Store\ProductDataDTO;
use App\Exceptions\ExternalApiException;
use App\Http\Controllers\Admin\AbstractCrudController;
use App\Http\Requests\Provisioning\StoreServiceRequest;
use App\Http\Requests\Provisioning\UpdateServiceRequest;
use App\Models\Account\Customer;
use App\Models\Core\Invoice;
use App\Models\Provisioning\CancellationReason;
use App\Models\Provisioning\Server;
use App\Models\Provisioning\Service;
use App\Models\Provisioning\ServiceRenewals;
use App\Models\Store\Product;
use App\Services\Core\InvoiceService;
use App\Services\Store\GatewayService;
use App\Services\Store\RecurringService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends AbstractCrudController
{

    protected string $viewPath = 'admin.provisioning.services';
    protected string $routePath = 'admin.services';
    protected string $translatePrefix = 'admin.services';
    protected string $model = Service::class;
    protected int $perPage = 25;
    protected string $searchField = 'id';
    protected array $filters = [
        'id',
        'customer_id',
        'name',
        'product_id',
    ];
    protected array $sorts = [
        'id',
        'customer_id',
        'status',
        'created_at',
    ];

    public function getIndexFilters()
    {
        return collect(Service::FILTERS)->mapWithKeys(function ($k, $v) {
            return [$k => __('global.states.' . $v)];
        })->toArray();
    }

    protected function getMassActions()
    {
        return [
            new MassActionDTO('suspend', __('admin.services.suspend.btn'), function (Service $service, ?string $reason = null) {
                return $service->suspend($reason ?? null);
            }, __('admin.services.suspend.reason')),
            new MassActionDTO('unsuspend', __('admin.services.unsuspend.btn'), function (Service $service) {
                return $service->unsuspend();
            }),
            new MassActionDTO('expire', __('admin.services.terminate.btn'), function (Service $service) {
                return $service->expire();
            }),
            new MassActionDTO('cancel', __('admin.services.cancel.btn'), function (Service $service, ?string $reason = null) {
                return $service->cancel($reason ?? 'Not specified', now(), true);
            }, __('client.services.cancel.reason')),
            new MassActionDTO('add_days', __('admin.services.add_days'), function (Service $service, ?string $days = null) {
                return $service->addDays((int)$days);
            }, __('admin.services.add_days_question')),

            new MassActionDTO('sub_days', __('admin.services.sub_days'), function (Service $service, ?string $days = null) {
                return $service->addDays((int)$days);
            }, __('admin.services.sub_days_question')),
            new MassActionDTO('deliver', __('admin.services.delivery.btn'), function (Service $service) {
                return $service->deliver();
            }),
            new MassActionDTO('delete', __('global.delete'), function (Service $service) {
                return $service->delete();
            }),

        ];
    }
    protected function getSearchFields()
    {
        return [
            'customer.email' => __('global.customer'),
            'id' => "Identifier",
            'name' => __('global.name'),
            'product_id' => __('global.product'),
        ];
    }

    public function show(Service $service)
    {
        $this->checkPermission('show', $service);
        $params['item'] = $service;
        $gateways = \App\Models\Core\Gateway::getAvailable()->get();
        $panel = $service->productType()->panel();
        if ($panel != null && !$service->isPending() && !$service->isExpired()) {
            $panel_html = $panel->renderAdmin($service);
            $tabs = $panel->tabs($service);
        } else {
            $panel_html = '';
            $tabs = [];
        }
        if ($service->isExpired()){
            $tabs = [];
        }
        $params['tabs'] = $tabs;
        $params['gateways'] = $gateways;
        $params['panel_html'] = $panel_html;
        $params['current_tab'] = null;
        $params['renewals'] = $service->renewals()->whereRaw('(renewed_at IS NOT NULL OR first_period = 1)')->orderBy('created_at', 'desc')->get();
        $params['statuses'] = Service::getStatuses();
        $params['products'] = $this->products();
        $params['invoices'] = $this->invoices($service);
        $params['invoices']->put('none', __('global.none'));
        $params['recurrings'] = $params['item']->pricingAvailable();
        $params['months'] = $this->months($params['recurrings']);
        $params['prices'] = $this->prices($service, $params['recurrings']);
        $params['servers'] = $this->servers($service->type);
        $params['types'] = $this->types();
        $params['cancellation_expirations'] = [
            'end_of_period' => __('client.services.cancel.expiration_end'),
            'now' => __('client.services.cancel.expiration_now'),
        ];
        $params['cancellation_reasons'] = \App\Models\Provisioning\CancellationReason::getAvailable(false)->pluck('reason', 'id')->mapWithKeys(function ($item, $key) {
            return [$key => $item];
        });
        if ($service->delivery_errors != null && $service->status == Service::STATUS_PENDING) {
            Session()->flash('warning', $service->delivery_errors);
        }
        if ($service->isExpired()) {
            \Session::flash('warning', __('admin.services.expired'));
        }

        if ($service->isCancelled() || $service->cancelled_at != NULL){
            if ($service->cancelled_at->isPast()){
                \Session::flash('warning', __('client.alerts.service_cancelled'));
            } else {
                \Session::flash('info', __('client.alerts.service_cancelled_and_not_expired'));
            }
        }
        return $this->showView($params);
    }

    public function create(Request $request)
    {
        $this->checkPermission('create');

        if ($request->query->count() != 0) {
            $types = app('extension')->getProductTypes()->keys()->merge(['none'])->toArray();
            $data = $request->only('customer_id', 'type', 'product_id');
            $data['product_id'] = ($data['product_id'] ?? 'none') == 'none' ? null : (int)$data['product_id'];
            $validator = \Validator::make($data, [
                'customer_id' => ['nullable', 'required', 'integer', Rule::exists('customers', 'id')],
                'type' => ['nullable', 'required', 'string', 'max:255', Rule::in($types)],
                'product_id' => ['nullable', 'integer', Rule::exists('products', 'id')],
            ]);
            if ($data['product_id'] != null){
                $product = Product::find($data['product_id']);
                if ($product->type != $data['type'] ?? 'none') {
                    return back()->with('error', __('admin.services.invalid_product_type', ['product' => $product->type, 'type' => $data['type']]));
                }
            }
            if ($validator->fails()) {
                return back()->with('error', __('admin.services.invalid_customer'));
            }
            $params['step'] = 2;
            $params['item'] = (new Service($data));
            $params['servers'] = $this->servers($data['type'] ?? 'none');
            $server = null;
            if (($product ?? null) !== null) {
                $productServer = $product->productType()->server();
                if ($productServer !== null) {
                    $server = $productServer->findServer($product);
                }
            }
            if ($server === null) {
                $server = Server::getAvailable()->where('type', $data['type'])->first();
            }
            if ($server != null) {
                $params['item']->fill(['server_id' => $server->id]);
            }
            $server = $params['item']->productType()->server();
            if ($server != null && $server->importService() != null) {
                $params['importHTML'] = $server->importService()->render($params['item'], $data);
            } else {
                $params['importHTML'] = '';
            }
            if ($server != null && $params['item']->productType()->data($product ?? null) != null) {
                $params['dataHTML'] = $params['item']->productType()->data($product ?? null)->render(new ProductDataDTO($params['item']->product ?? (new Product(['id' => -1])), $request->all() + ['in_admin' => true, 'service_creation' => true], [], []));
            } else {
                $params['dataHTML'] = '';
            }
            $params['item']->fill(['expires_at' => Carbon::now()->addDays(30)]);
            $params['recurrings'] = $params['item']->pricingAvailable();
            if ($params['item']->product_id != null) {
                $params['item']->fill(['name' => $params['item']->product->name]);
                $price = current($params['item']->pricingAvailable(true));
                $params['item']->fill(['price' => $price->dbprice, 'currency' => $price->currency, 'initial_price' => $price->price]);
            }
            $params['customer_id'] = $request->get('customer_id');
        } else {
            $params['products'] = $this->products();
            $params['types'] = $this->types();
            $params['product_id'] = current($params['products']->keys());
            $params['step'] = 1;
            $params['dataHTML'] = '';
            $params['customers'] = $this->customers();
        }

        return $this->createView($params);
    }

    public function store(StoreServiceRequest $request)
    {
        $this->checkPermission('create');
        $service = Service::create($request->validated());
        if (array_key_exists('import', $request->all())){
            return $this->import($request, $service);
        }
        if (array_key_exists('create', $request->all())) {
            return $this->createNew($request, $service);
        }
    }

    public function tab(Service $service, string $tab)
    {
        $gateways = GatewayService::getAvailable($service->price);
        $panel = $service->productType()->panel();
        if ($panel == null){
            return redirect()->route('admin.services.show', ['service' => $service->id]);
        }
        $tabs = $panel->tabs($service);

        if (!empty($tabs)){
            array_unshift($tabs, new ProvisioningTabDTO([
                'title' => __('global.service'),
                'permission' => 'service.show',
                'icon' => '<i class="bi bi-info-circle"></i>',
                'uuid' => 'services',
                'active' => true,
            ]));
        }
        if ($service->isExpired()){
            $tabs = [];
        }
        $current_tab = collect($tabs)->first(function ($value, $key) use ($tab) {
            return $value->uuid == $tab && $value->active;
        });
        abort_if(!$current_tab, 404);

        $tab_html = $current_tab->renderTab($service, $tab);
        if ($tab_html instanceof \Illuminate\Http\Response || $tab_html instanceof \Illuminate\Http\RedirectResponse) {
            return $tab_html;
        }
        $params['item'] = $service;
        $params['tabs'] = $tabs;
        $params['gateways'] = $gateways;
        $params['panel_html'] = $tab_html;
        $params['current_tab'] = $current_tab;
        $params['renewals'] = $service->renewals();
        $params['recurrings'] = $params['item']->pricingAvailable();
        $params['months'] = $this->months($params['recurrings']);
        $params['invoices'] = $this->invoices($service);
        $params['invoices']->put('none', __('global.none'));
        $params['prices'] = $this->prices($service, $params['recurrings']);
        $params['intab'] = true;
        $params['cancellation_expirations'] = [
            'end_of_period' => __('client.services.cancel.expiration_end'),
            'now' => __('client.services.cancel.expiration_now'),
        ];
        $params['cancellation_reasons'] = \App\Models\Provisioning\CancellationReason::getAvailable(false)->pluck('reason', 'id')->mapWithKeys(function ($item, $key) {
            return [$key => $item];
        });
        return $this->showView($params);
    }

    public function updateData(Request $request, Service $service)
    {
        $this->checkPermission('update', $service);
        $validated = $request->validate([
            'data' => 'required|json',
        ]);
        $service->data = json_decode($validated['data']);
        $service->save();
        return $this->updateRedirect($service);
    }

    public function renew(Request $request, Service $service)
    {
        $this->checkPermission('create_invoices', $service);
        $gateway = \App\Models\Core\Gateway::getAvailable()->first();
        if (!$gateway) {
            return back()->with('error', __('admin.services.no_gateway'));
        }
        if ($service->invoice_id != null){
            ServiceRenewals::where('invoice_id', $service->invoice_id)->delete();
            $service->invoice->cancel();
            $service->invoice_id = null;
            $service->save();
            return back()->with('success', __('admin.services.renewals.removed'));

        } else {
            $service->billing = $request->get('billing');
            if ($request->get('invoice_id') != 'none'){
                $invoice = Invoice::find($request->get('invoice_id'));
                if ($invoice == null){
                    return back()->with('error', __('admin.services.invoice_not_found'));
                }
                InvoiceService::appendServiceOnExistingInvoice($service, $invoice);
                $service->invoice_id = $invoice->id;
                $service->billing = $service->getOriginal('billing');
                $service->save();
            } else {
                $invoice = InvoiceService::createInvoiceFromService($service);
                $service->invoice_id = $invoice->id;
                $service->billing = $service->getOriginal('billing');
                $service->save();
            }
        }
        return redirect()->route('admin.invoices.show', ['invoice' => $invoice->id]);
    }

    public function changeStatus(Request $request, Service $service, string $status)
    {
        $this->checkPermission('update', $service);
        if (!in_array($status, ['suspend', 'unsuspend', 'expire', 'cancel'])){
            return back()->with('error', __('admin.services.invalid_status'));
        }
        if ($status == 'unsuspend'){
            $result = $service->unsuspend();
        } else if ($status == 'suspend'){
            $result = $service->suspend(!empty($request->get('reason')) ? $request->get('reason') : null, $request->has('notify'));
        } else if ($status == 'expire') {
            $result = $service->expire(true);
            $status = 'terminate';
        } elseif ($status == 'cancel') {
            if ($service->cancelled_at != null){
                $result = $service->uncancel();
                $status = 'uncancel';
            } else {
                $reason = CancellationReason::find($request->get('reason'))->reason ?? null;
                $date = $request->expiration == 'end_of_period' ? $service->expires_at : new \DateTime();
                $result = $service->cancel($reason,$date, $request->get('expiration') == 'now');
            }
        } else {
            $result = new ServiceStateChangeDTO($service, false, 'Invalid status');
        }
        if ($result->success){
            return back()->with('success', __('admin.services.' . $status . '.success'));
        } else {
            return back()->with('error', __('admin.services.status_change_failed', ['error' => $result->message]));
        }
    }

    public function update(UpdateServiceRequest $request, Service $service)
    {
        $this->checkPermission('update', $service);
        if ($service->product_id != null) {
            $productPrice = $service->product->getPriceByCurrency($service->currency, $service->billing)->price;
        } else {
            $productPrice = null;
        }
        $service->fill($request->validated());
        if ($service->product_id != null && $productPrice == $service->price){
            $service->price = $service->product->getPriceByCurrency($service->currency, $request->billing)->price;
        }
        if ($service->status == 'cancelled' && $service->cancelled_at == null){
            $service->cancelled_at = now();
        }
        if ($service->getAttribute('expires_at') != $service->getOriginal('expires_at') && $service->expires_at != null){
            $service->last_expires_at = $service->expires_at;
            $server = $service->productType()->server();
            try {
                if ($server != null){
                    $server->onRenew($service);
                }
            } catch (ExternalApiException $e){
                \Session::flash('error', $e->getMessage());
            }
        }
        $service->save();
        return $this->updateRedirect($service);
    }

    public function delivery(Service $service)
    {
        $this->checkPermission('deliver_services', $service);
        if ($service->isPending()) {
            try {

                $result = $service->deliver();
                if ($result->success) {
                    return back()->with('success', __('admin.services.delivery.success'));
                } else {
                    return back()->with('error', $result->message);
                }
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        }
        return back()->with('error', __('admin.services.delivery.not_pending'));
    }

    public function reinstall(Service $service): \Illuminate\Http\RedirectResponse
    {
        $this->checkPermission('deliver_services', $service);
        if ($service->isPending()) {
            try {
                $result = $service->expire(true);
                if ($result->success) {
                    $service->status = Service::STATUS_PENDING;
                    $service->save();
                    return back()->with('info', __('admin.services.delivery.reinstall'));
                } else {
                    $service->status = Service::STATUS_PENDING;
                    $service->save();
                    return back()->with('info', __('admin.services.delivery.reinstall'))->with('error', $result->message);
                }
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        }
        return back()->with('error', __('admin.services.delivery.not_pending'));
    }

    private function products()
    {
        $products = Product::getAllProducts(true);
        $products->put('none', __('global.none'));
        return $products;
    }

    private function servers(string $type)
    {
        if ($type == 'none') {
            $servers = Server::getAvailable(true)->get()->pluck('name', 'id');
        } elseif ($type == 'pterobox'){
            $servers = Server::all()->whereIn('type', ['pterobox', 'wisp', 'pterodactyl'])->pluck('name', 'id');

        }else {
            $servers = Server::all()->where('type', $type)->pluck('name', 'id');
        }
        $servers->put('none', __('global.none'));
        return $servers;
    }

    private function invoices(Service $service)
    {
        return $service->customer->invoices()->where('status', Invoice::STATUS_PENDING)->orderBy('created_at', 'desc')->get()->pluck('id', 'total')->mapWithKeys(function ($id, $price) {
            return [$id => __('global.invoice') . ' #' . $id . ' - ' . formatted_price($price)];
        });
    }

    private function types()
    {
        return app('extension')->getProductTypes()->keys()->merge(['none'])->mapWithKeys(function ($k) {
            return [$k => $k];
        });
    }

    private function months(array $recurrings)
    {
        return collect(app(RecurringService::class)->getRecurrings())->filter(function($k, $v) use ($recurrings) {
            return in_array($v, array_keys($recurrings)) && $v != 'onetime';
        });
    }

    private function customers()
    {
        return Customer::select(['id', 'email', 'firstname', 'lastname'])->get()->mapWithKeys(function(Customer $customer) {
            return [$customer->id => $customer->email];
        });
    }

    private function prices(Service $service, array $recurrings)
    {
        if ($service->product_id != null){
            return collect($recurrings)->map(function($k, $v) use ($service){
                return $service->renewPrice($service->currency, $v);
            })->toArray();
        } else {
            return collect(app(RecurringService::class)->getRecurrings())->map(function($k, $v) use ($service) {
                return $service->renewPrice($service->currency, $v);
            })->toArray();
        }
    }


    public function search(Request $request)
    {
        $q = $request->get('q');
        if (filter_var($q, FILTER_VALIDATE_EMAIL)) {
            $customer = Customer::select('id')->where('email', $q)->first();
            if ($customer == null){
                return $this->model::where('id', -1)->paginate($this->perPage);
            }
            return $this->model::where('customer_id', $customer->id)->paginate($this->perPage);
        }
        return $this->model::where('id', $q)->paginate($this->perPage);
    }

    public function destroy(Service $service)
    {
        $this->checkPermission('delete', $service);
        $result = $service->expire();
        $service->delete();
        if (!$result->success){
            \Session::flash('error', __('admin.services.delete_failed', ['error' => $result->message]));
        }
        return $this->deleteRedirect($service);
    }

    private function import(Request $request, Service $service)
    {
        $this->checkPermission('create');
        if ($service->productType()->server() != null && $service->productType()->server()->importService() != null) {
            $validator = \Validator::make($request->all(), $service->productType()->server()->importService()->validate());
            if ($validator->fails()) {
                return back()->with('error', join('<br>', $validator->errors()->all()));
            }
            /** @var ServiceStateChangeDTO $result */
            $result = $service->productType()->server()->importService()->import($service, $validator->validated() + $request->validated());
            if ($result->success){
                $service->delivery_errors = null;
                $service->status = Service::STATUS_ACTIVE;
                $service->save();
                return redirect()->route('admin.services.show', ['service' => $service->id])->with('success', __('admin.services.imported'));
            } else {
                return back()->with('error', $result->message);
            }
        }
        return redirect()->route('admin.services.show', ['service' => $service->id])->with('success', __('admin.services.imported'));
    }

    private function createNew(StoreServiceRequest $request, Service $service)
    {
        $this->checkPermission('create');
        $service->attachMetadata('service_created_by', auth('admin')->id());
        $service->attachMetadata('service_created_at', now());
        $service->save();
        if ($service->productType()->data($service->product ?? null) != null) {
            $validator = \Validator::make($request->all(), $service->productType()->data($service->product ?? null)->validate());
            if ($validator->fails()) {
                return back()->with('error', join('<br>', $validator->errors()->all()));
            }
            $service->data = $service->productType()->data($service->product ?? null)->parameters(new ProductDataDTO($service->product ?? (new Product(['id' => -1])), $request->all() + ['in_admin' => true, 'service_creation' => true], $validator->validated()));
            $service->save();
            /** @var ServiceStateChangeDTO $result */
            $result = $service->deliver();
            if ($result->success) {
                $service->delivery_errors = null;
                $service->status = Service::STATUS_ACTIVE;
                $service->save();
            }
        }
        return $this->storeRedirect($service)->with('success', __('admin.services.create.created'));
    }

}
