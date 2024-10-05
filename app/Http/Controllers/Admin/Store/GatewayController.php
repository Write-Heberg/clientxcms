<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin\Store;

use App\Exceptions\PaymentConfigException;
use App\Http\Controllers\Controller;
use App\Models\Core\Gateway;
use App\Services\Store\GatewayService;
use Illuminate\Http\Request;

class GatewayController extends Controller
{
    public function config()
    {
        $parameters = request()->route()->parameters();
        $uuid = $parameters['uuid'];
        $gateway = Gateway::where('uuid', $uuid)->firstOrFail();
        $paymentType = $gateway->paymentType();
        $config = $paymentType->configForm();
        $statusOptions = [
            'hidden' => __('global.hidden'),
            'active' => __('global.active'),
            'unreferenced' => __('global.unreferenced'),
        ];
        return view('admin.settings.store.gateways.config', compact('config', 'gateway', 'statusOptions'));
    }

    public function saveConfig(Request $request, Gateway $gateway)
    {
        $paymentType = $gateway->paymentType();
        $this->validate($request, ['status' => 'required', 'name' => 'required']);
        $gateway->update($request->only(['status', 'name']));
        if ($request->status !== 'hidden') {
            $validated = $this->validate($request, $paymentType->validate());
            try {
                $paymentType->saveConfig($validated);
            } catch (PaymentConfigException $e){
                return redirect()->back()->with('error', $e->getMessage());
            }
        }
        GatewayService::forgotAvailable();
        return redirect()->back()->with('success', __('admin.settings.store.gateways.success'));
    }
}
