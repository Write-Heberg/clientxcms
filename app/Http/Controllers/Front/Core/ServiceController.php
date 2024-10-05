<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Front\Core;

use App\DTO\Provisioning\ProvisioningTabDTO;
use App\Exceptions\WrongPaymentException;
use App\Helpers\Countries;
use App\Http\Controllers\Controller;
use App\Models\Core\Invoice;
use App\Models\Core\InvoiceItem;
use App\Models\Provisioning\Service;
use App\Services\Core\InvoiceService;
use App\Services\Store\GatewayService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('filter')) {
            $filter = $request->get('filter');
            if (!in_array($filter, array_keys(Service::FILTERS))){
                return redirect()->route('front.services.index');
            }
            $services = Service::where('customer_id', auth()->id())->where('status', $request->get('filter'))->orderBy('created_at', 'desc')->paginate(10);
        } else {
            $filter = null;
            $services = Service::where('customer_id', auth()->id())->orderBy('created_at', 'desc')->paginate(10);
        }
        return view('front.client.services.index', [
            'services' => $services,
            'filter' => $filter,
            'filters' => Invoice::FILTERS,
            'gateways' => GatewayService::getAvailable(1),
        ]);
    }

    public function show(Service $service)
    {
        abort_if($service->customer_id != auth()->id(), 404);
        abort_if($service->state == 'pending', 404);
        $customer = $service->customer;
        $gateways = \App\Models\Core\Gateway::getAvailable()->get();
        $panel = $service->productType()->panel();
        if ($panel != null  && $service->isActivated() && !$service->isExpired()){
            $panel_html = $panel->render($service);
            if ($service->isActivated()){
                $tabs = collect($panel->tabs($service))->filter(function ($value) { return $value->admin == false; })->toArray();
            } else {
                $tabs = [];
            }
        } else {
            $panel_html = '';
            $tabs = [];
        }
        if ($service->isExpired()){
            $tabs = [];
        }
        if ($service->invoice_id != null && $service->canRenew()){
            \Session::flash('warning', __('client.alerts.service_not_paid', ['url' => route('front.invoices.show', ['invoice' => $service->invoice_id])]));
        }
        if ($service->isCancelled() || $service->cancelled_at != NULL){
            if ($service->cancelled_at->isPast()){
                \Session::flash('warning', __('client.alerts.service_cancelled'));
            } else {
                \Session::flash('info', __('client.alerts.service_cancelled_and_not_expired'));
            }
        }

        $cancellation_reasons = \App\Models\Provisioning\CancellationReason::getAvailable(false)->pluck('reason', 'id')->mapWithKeys(function ($item, $key) {
            return [$key => $item];
        });
        $cancellation_expirations = [
            'end_of_period' => __('client.services.cancel.expiration_end'),
            'now' => __('client.services.cancel.expiration_now'),
        ];
        $current_tab = null;
        return view('front.client.services.show', compact('cancellation_expirations', 'cancellation_reasons', 'current_tab','tabs', 'service', 'customer', 'gateways', 'panel_html'));
    }

    public function renew(Request $request, Service $service, string $gateway)
    {
        if (!auth('web')->user()->hasServicePermission($service, 'service.renew')){
            abort(404);
        }
        abort_if($service->customer_id != auth()->id(), 404);
        if (!$service->canRenew()){
            return redirect()->route('front.services.show', ['service' => $service->id])->with('error', __('client.alerts.cannot_renew'));
        }
        $gateway = \App\Models\Core\Gateway::getAvailable()->where('uuid', $gateway)->first();
        abort_if(!$gateway, 404);
        if ($service->invoice_id != null){
            $invoice = $service->invoice;
        } else {
            $invoice = InvoiceService::createInvoiceFromService($service);
            $service->invoice_id = $invoice->id;
            $service->save();
        }
        return $invoice->pay($gateway, $request);
    }

    public function tab(Service $service, string $tab)
    {
        abort_if($service->customer_id != auth()->id(), 404);
        $customer = $service->customer;
        $gateways = GatewayService::getAvailable($service->price);
        $panel = $service->productType()->panel();
        if ($panel == null){
            return redirect()->route('front.services.show', ['service' => $service->id]);
        }
        $tabs = $panel->tabs($service);
        $tabs = collect($tabs)->filter(function ($value) {
            return $value->admin == false;
        })->toArray();
        if (!empty($tabs)) {
            array_unshift($tabs, new ProvisioningTabDTO([
                'title' => __('global.service'),
                'permission' => 'service.show',
                'icon' => '<i class="bi bi-info-circle"></i>',
                'uuid' => 'services',
                'active' => true,
            ]));
        }
        $current_tab = collect($tabs)->filter(function ($value) {
            return $value->admin == false;
        })->first(function ($value, $key) use ($tab) {
            return $value->uuid == $tab && $value->active;
        });
        abort_if(!$current_tab, 404);
        $panel_html = '';
        if ($service->isActivated()) {
            $panel_html = $current_tab->renderTab($service, $tab);
        }
        if ($panel_html instanceof \Illuminate\Http\Response || $panel_html instanceof \Illuminate\Http\RedirectResponse) {
            return $panel_html;
        }
        $cancellation_reasons = \App\Models\Provisioning\CancellationReason::getAvailable(false)->pluck('reason', 'id')->mapWithKeys(function ($item, $key) {
            return [$key => $item];
        });
        $cancellation_expirations = [
            'end_of_period' => __('client.services.cancel.expiration_end'),
            'now' => __('client.services.cancel.expiration_now'),
        ];
        if ($service->isExpired()){
            $tabs = [];
        }
        return view('front.client.services.show', compact('cancellation_reasons', 'cancellation_expirations', 'tabs', 'current_tab', 'service', 'customer', 'gateways', 'panel_html'));
    }

    public function renewal(Service $service)
    {
        if (!auth('web')->user()->hasServicePermission($service, 'service.renewal')){
            abort(404);
        }
        $customer = $service->customer;
        $gateways = GatewayService::getAvailable($service->price);
        $renewals = $service->renewals()->whereNotNull('renewed_at')->orderBy('created_at', 'desc')->paginate(10);
        return view('front.client.services.renewal', compact('gateways', 'service', 'customer', 'renewals'));
    }

    public function billing(Request $request, Service $service)
    {
        if (!auth('web')->user()->hasServicePermission($service, 'service.billing')){
            abort(404);
        }
        $this->validate($request, [
            'billing' => 'required|string',
        ]);
        $service->billing = $request->get('billing');
        $service->price = collect($service->pricingAvailable(true))->filter(function ($pricing) use ($service) {
            return $pricing->recurring == $service->billing;
        })->first()->dbprice;
        $service->save();
        $service->attachMetadata('default_price', $service->price);
        $service->price = $service->generateDiscountedPrice($request->get('billing'));
        $service->save();
        if ($request->has('pay')){
            return redirect()->route('front.services.renew', ['service' => $service->id, 'gateway' => $request->get('gateway')]);
        }
        return back()->with('success', __('client.alerts.service_billing_updated'));
    }

    public function name(Request $request, Service $service)
    {
        if (!auth('web')->user()->hasServicePermission($service, 'service.name')){
            abort(404);
        }
        $this->validate($request, [
            'name' => 'required|string|max:50',
        ]);
        $service->name = $request->get('name');
        $service->save();
        return redirect()->route('front.services.show', ['service' => $service->id])->with('success', __('client.alerts.service_name_updated'));
    }

    public function cancel(Request $request, Service $service)
    {

        if (!auth('web')->user()->hasServicePermission($service, 'service.cancel')){
            abort(404);
        }
        if ($service->isOneTime()){
            $request->params->set('expiration', 'now');
        }
        if ($service->cancelled_at != null){
            $service->uncancel();
            return redirect()->route('front.services.show', ['service' => $service->id])->with('success', __('client.alerts.service_uncancelled'));
        }
        $request->validate([
            'reason' => ['required', 'string', 'exists:cancellation_reasons,id'],
            'details' => 'nullable|string',
            'expiration' => ['required', 'string', 'in:end_of_period,now']
        ]);
        if (!$service->canCancel()) {
            return redirect()->route('front.services.show', ['service' => $service->id])->with('error', __('client.alerts.cannot_cancel'));
        }
        $reason = \App\Models\Provisioning\CancellationReason::find($request->reason)->reason;
        $reason = $reason . (!empty($request->details) ? ' - ' . $request->details : '');
        $date = $request->expiration == 'end_of_period' ? $service->expires_at : new \DateTime();
        $service->cancel($reason, $date, $request->expiration == 'now');
        return redirect()->route('front.services.show', ['service' => $service->id])->with('success', __('client.alerts.service_cancelled'));
    }


}
