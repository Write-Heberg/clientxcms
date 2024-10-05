<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Provisioning\Service;
use App\Modules\Proxmox\DTO\ProxmoxVPSDTO;
use App\Modules\Proxmox\Models\ProxmoxOS;
use App\Modules\Proxmox\Models\ProxmoxTemplates;
use App\Modules\Proxmox\Requests\ReinstallRequest;

class PowerController extends Controller
{
    public function power(Service $service, string $power)
    {
        if (!auth('admin')->check()){
            if (auth('web')->guest()){
                abort(404);
            }
            if (!auth('web')->user()->hasServicePermission($service, 'proxmox.power')){
                abort(404);
            }
        }
        abort_if(!in_array($power, ['start', 'stop', 'reboot']), 404);
        abort_if($service->type != 'proxmox', 404);

        try {
            $vps = new ProxmoxVPSDTO($service->getMetadata('vmid'), $service->getMetadata('type'), $service->getMetadata('node'), $service->server);
        } catch (\Exception $e) {
            \Session::flash('error', __('client.alerts.vpsnotfound'));
            return back();
        }

        if ($vps->hasCorrectTags($service->id) === false) {
            \Session::flash('error', __('client.alerts.vpsnotfound'));
            return back();
        }
        try {
            $result = $vps->{$power}($service);
            if ($result == null){
                return back();
            }
            if ($result->status() == 200) {
                sleep(3);
                return back()->with('success', __('client.alerts.power_success'));
            }
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            return back()->with('error', __('client.alerts.power_error'));
        }
    }

    public function reinstall(ReinstallRequest $request, Service $service)
    {
        if (!auth('admin')->check()){
            if (auth('web')->guest()){
                abort(404);
            }
            if (!auth('web')->user()->hasServicePermission($service, 'proxmox.reinstall')){
                abort(404);
            }
        }
        abort_if($service->type != 'proxmox', 404);
        try {
            $vps = new ProxmoxVPSDTO($service->getMetadata('vmid'), $service->getMetadata('type'), $service->getMetadata('node'), $service->server);
        } catch (\Exception $e) {
            \Session::flash('error', __('client.alerts.vpsnotfound'));
            return back();
        }

        if ($vps->hasCorrectTags($service->id) === false) {
            \Session::flash('error', __('client.alerts.vpsnotfound'));
            return back();
        }
        if (!$vps->canReinstall($service)) {
            \Session::flash('error', __('proxmox::messages.reinstallation.limited'));
            return back();
        }
        try {
            $template = null;
            $ose = null;
            if ($request->ose)
                $ose = ProxmoxOS::find($request->ose);
            if ($request->template)
                $template = ProxmoxTemplates::find($request->template);
            $vps->reinstall($service, $request->password, $request->hostname, $ose, $template);
            return back()->with('success', __('proxmox::messages.reinstallation.reinstall_success'));
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            return back()->with('error', __('proxmox::messages.reinstallation.reinstall_error'));
        }
    }
}
