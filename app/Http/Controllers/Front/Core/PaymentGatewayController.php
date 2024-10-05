<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Front\Core;

use App\Exceptions\WrongPaymentException;
use App\Http\Controllers\Controller;
use App\Models\Core\Gateway;
use App\Models\Core\Invoice;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{

    public function notification(Request $request, string $gateway)
    {
        try {
            $gateway = Gateway::where('uuid', $gateway)->first();
            abort_if(!$gateway, 404);
            return $gateway->paymentType()->notification($gateway, $request);
        } catch (WrongPaymentException $e) {
            logger()->error($e->getMessage());
            return abort(404);
        }
    }

    public function cancel(Invoice $invoice)
    {
        $invoice->cancel();
        return redirect()->route('front.invoices.show', $invoice->id)->with('warning', __('global.invoice_was_cancelled'));
    }

    public function return(Request $request, Invoice $invoice, string $gateway)
    {
        try {
            $gateway = Gateway::where('uuid', $gateway)->first();
            abort_if(!$gateway, 404);
            return $gateway->processPayment($invoice, $request);
        } catch (WrongPaymentException $e) {
            logger()->error($e->getMessage());
            return redirect()->route('front.invoices.show', $invoice->id)->with('error', __('store.checkout.wrong_payment'));
        }
    }
}
