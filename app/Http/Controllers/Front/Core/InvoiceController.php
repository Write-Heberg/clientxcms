<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Front\Core;

use App\Exceptions\WrongPaymentException;
use App\Helpers\Countries;
use App\Http\Controllers\Controller;
use App\Models\Core\Invoice;
use App\Services\Store\GatewayService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('filter')) {
            $filter = $request->get('filter');
            if (!in_array($filter, array_keys(Invoice::FILTERS))){
                return redirect()->route('front.invoices.index');
            }
            $invoices = Invoice::where('customer_id', auth()->id())->where('status', '!=', Invoice::STATUS_DRAFT)->where('status', $request->get('filter'))->orderBy('created_at', 'desc')->paginate(10);
        } else {
            $filter = null;
            $invoices = Invoice::where('customer_id', auth()->id())->where('status', '!=', Invoice::STATUS_DRAFT)->orderBy('created_at', 'desc')->paginate(10);
        }

        return view('front.client.invoices.index', [
            'invoices' => $invoices,
            'filter' => $filter,
            'filters' => Invoice::FILTERS
        ]);
    }

    public function show(Invoice $invoice)
    {
        abort_if($invoice->customer_id != auth()->id(), 404);

        $customer = $invoice->customer;
        $countries = Countries::names();
        $gateways = GatewayService::getAvailable($invoice->total);
        if ($invoice->isDraft()){
            return abort(404);
        }
        return view('front.client.invoices.show', compact('invoice', 'customer', 'countries', 'gateways'));
    }

    public function pay(Invoice $invoice, string $gateway)
    {
        abort_if($invoice->customer_id != auth()->id(), 404);
        $gateway = \App\Models\Core\Gateway::getAvailable()->where('uuid', $gateway)->first();
        if ($gateway === null) {
            return redirect()->route('front.invoices.show', $invoice)->with('error', __('store.checkout.gateway_not_found'));
        }
        try {
            if ($invoice->canPay()){
                return $invoice->pay($gateway, request());
            }
            return redirect()->route('front.invoices.show', $invoice)->with('error', __('client.invoices.invoice_not_payable'));
        } catch (WrongPaymentException $e) {
            logger()->error($e->getMessage());
            return redirect()->route('front.invoices.show', $invoice)->with('error', __('store.checkout.wrong_payment'));
        }
    }

    public function download(Invoice $invoice)
    {
        abort_if($invoice->customer_id != auth()->id(), 404);
        return $invoice->download();
    }
}
