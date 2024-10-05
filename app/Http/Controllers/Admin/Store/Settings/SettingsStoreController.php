<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin\Store\Settings;

use App\Http\Controllers\Controller;
use App\Models\Admin\Setting;
use App\Models\Core\Invoice;
use App\Services\Core\InvoiceService;
use App\Services\Store\CurrencyService;
use App\Services\Store\TaxesService;
use Illuminate\Http\Request;

class SettingsStoreController extends Controller
{

    public function showBilling()
    {
        $currencies = (new CurrencyService())->getCurrencies()->mapWithKeys(function ($item, $key) {
            return [$key => $item['label']];
        });
        $billing_modes = [
            InvoiceService::INVOICE => __('admin.settings.store.checkout.fields.billing_modes.invoice'),
            InvoiceService::PRO_FORMA => __('admin.settings.store.checkout.fields.billing_modes.proforma'),
        ];
        $options = [TaxesService::MODE_TAX_EXCLUDED => __('admin.settings.store.checkout.fields.mode_tax.included'), TaxesService::MODE_TAX_INCLUDED => __('admin.settings.store.checkout.fields.mode_tax.excluded')];
        return view('admin/settings/store/checkout', compact('billing_modes', 'options', 'currencies'));
    }
    public function saveBilling(Request $request)
    {
        $validated = $this->validate($request, [
            'store_mode_tax' => 'in:tax_included,tax_excluded',
            'checkout_customermustbeconfirmed' => 'in:true,false',
            'checkout_toslink' => 'nullable|string|url',
            'store_checkout_webhook_url' => 'nullable|string|url',
            'store_vat_enabled' => 'in:true,false',
            'store_currency' => ['required'],
            'invoice_terms' => 'string|max:1000',
            'app_address' => 'required|string|max:255',
            'billing_invoice_prefix' => 'required|string|max:10',
            'billing_mode' => 'required|in:invoice,proforma',
            'remove_pending_invoice' => 'required|integer|min:0',
            'remove_pending_invoice_type' => 'required|in:cancel,delete',
        ]);
        $validated['store_vat_enabled'] = $validated['store_vat_enabled'] ?? 'false';
        $validated['checkout_customermustbeconfirmed'] = $validated['checkout_customermustbeconfirmed'] ?? 'false';
        if (\setting('billing_invoice_prefix') !== $validated['billing_invoice_prefix']) {
            Invoice::updateInvoicePrefix($validated['billing_invoice_prefix']);
        }
        Setting::updateSettings($validated);
        return redirect()->back()->with('success', __('admin.settings.store.checkout.success'));

    }
}
