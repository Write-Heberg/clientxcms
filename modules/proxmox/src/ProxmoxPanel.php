<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox;

use App\DTO\Provisioning\ProvisioningTabDTO;
use App\Models\Provisioning\Service;
use App\Modules\Proxmox\DTO\ProxmoxVPSDTO;
use App\Modules\Proxmox\Models\ProxmoxLogs;

class ProxmoxPanel extends \App\Abstracts\AbstractPanelProvisioning
{

    protected string $uuid = 'proxmox';
    public function render(Service $service, array $permissions = [])
    {
        if (!$service->server){
            \Session::flash('error', __('client.alerts.servernotfound'));
            return '';
        }
        try {
            $vmid = $service->getMetadata('vmid');
            $node = $service->getMetadata('node') ?? 'pve';
            $type = $service->getMetadata('type') ?? 'lxc';
            if (($service->getMetadata('proxmox_reinstall') ?? 'false') == 'true'){
                \Session::flash('error', __('client.alerts.servernotinstalled'));
                return '';
            }
            if ($vmid == null || $node == null || $type == null){
                \Session::flash('error', __('client.alerts.vpsnotfound') . ' (metadata)');
                return '';
            }
            $vps = new ProxmoxVPSDTO($vmid, $type, $node, $service->server);
            $resources = $vps->getResourceUsage();
            if ($resources == null){
                \Session::flash('error', __('client.alerts.vpsnotfound') . ' (resources)');
                return '';
            }
            try {
                $vps->savePrimaryIp($service->id);
            } catch (\Exception $e) {
            }
        } catch (\Exception $e) {
            \Session::flash('error', __('client.alerts.vpsnotfound') .  ($permissions['in_admin'] ?? false) ? ' (' . $e->getMessage() . ')' : '');
            return '';
        }

        if ($vps->hasCorrectTags($service->id) === false) {
            \Session::flash('error', __('client.alerts.vpsnotfound') . ' (tags)');
            return '';
        }

        return view('proxmox::panel/index', [
            'service' => $service,
            'permissions' => $permissions,
            'resources' => $resources,
            'vps' => $vps,
        ]);
    }

    public function renderAdmin(Service $service)
    {
        return $this->render($service, ['in_admin' => true]);
    }

    public function tabs(Service $service): array
    {
        return [
            new ProvisioningTabDTO([
                'title' => __('proxmox::messages.logs.title'),
                'permission' => 'proxmox.panel.logs',
                'icon' => '<i class="bi bi-archive"></i>',
                'uuid' => 'logs',
                'active' => true,
            ]),
            new ProvisioningTabDTO([
                'title' => __('proxmox::messages.reinstallation.title'),
                'permission' => 'proxmox.panel.reinstallation',
                'icon' => '<i class="bi bi-arrow-repeat"></i>',
                'uuid' => 'reinstallation',
                'active' => true,
            ]),
            new ProvisioningTabDTO([
                'title' => __('proxmox::messages.graphs.title'),
                'permission' => 'proxmox.panel.graphs',
                'icon' => '<i class="bi bi-bar-chart"></i>',
                'uuid' => 'graphs',
                'active' => true,
            ])
            /*new ProvisioningTabDTO([
                'title' => __('proxmox::messages.console.title'),
                'permission' => 'proxmox.panel.console',
                'icon' => '<i class="bi bi-terminal"></i>',
                'uuid' => 'console',
                'popup' => true,
                'active' => false,
            ]),*/
        ];
    }

    public function renderLogs(Service $service, array $permissions = [])
    {

        if (!$service->server){
            \Session::flash('error', __('client.alerts.servernotfound'));
            return '';
        }

        if (($service->getMetadata('proxmox_reinstall') ?? 'false') == 'true'){
            \Session::flash('error', __('client.alerts.servernotinstalled'));
            return '';
        }
        try {
            $vps = new ProxmoxVPSDTO($service->getMetadata('vmid'), $service->getMetadata('type'), $service->getMetadata('node'), $service->server);
        } catch (\Exception $e) {
            \Session::flash('error', __('client.alerts.vpsnotfound'));
            return '';
        }

        if ($vps->hasCorrectTags($service->id) === false) {
            \Session::flash('error', __('client.alerts.vpsnotfound'));
            return '';
        }

        return view('proxmox::panel/logs', [
            'service' => $service,
            'permissions' => $permissions,
            'vps' => $vps,
            'logs' => ProxmoxLogs::where('service_id', $service->id)->orderBy('id', 'desc')->paginate(50),
        ]);
    }

    public function renderGraphs(Service $service, array $permissions = [])
    {
        if (!$service->server){
            \Session::flash('error', __('client.alerts.servernotfound'));
            return '';
        }

        if (($service->getMetadata('proxmox_reinstall') ?? 'false') == 'true'){
            \Session::flash('error', __('client.alerts.servernotinstalled'));
            return '';
        }
        try {
            $vps = new ProxmoxVPSDTO($service->getMetadata('vmid'), $service->getMetadata('type'), $service->getMetadata('node'), $service->server);
            $resources = $vps->getResourceUsage();
        } catch (\Exception $e) {
            \Session::flash('error', __('client.alerts.vpsnotfound'));
            return '';
        }

        if ($vps->hasCorrectTags($service->id) === false) {
            \Session::flash('error', __('client.alerts.vpsnotfound'));
            return '';
        }
        if (request()->has('timeframe')) {
            if (in_array(request('timeframe'), ['hour', 'day', 'week', 'month'])) {
                $vps->timeframe = request('timeframe');
            }
        }

        return view('proxmox::panel/graphs', [
            'service' => $service,
            'permissions' => $permissions,
            'vps' => $vps,
            'resources' => $resources,
            'timeframes' => [
                'hour' => __('proxmox::messages.graphs.timeframes.hour'),
                'day' => __('proxmox::messages.graphs.timeframes.day'),
                'week' => __('proxmox::messages.graphs.timeframes.week'),
                'month' => __('proxmox::messages.graphs.timeframes.month'),
            ],
        ]);
    }

    public function renderReinstallation(Service $service, array $permissions = [])
    {
        if (!$service->server){
            \Session::flash('error', __('client.alerts.servernotfound'));
            return '';
        }

        if (($service->getMetadata('proxmox_reinstall') ?? 'false') == 'true'){
            \Session::flash('error', __('client.alerts.servernotinstalled'));
            return '';
        }
        try {
            $vps = new ProxmoxVPSDTO($service->getMetadata('vmid'), $service->getMetadata('type'), $service->getMetadata('node'), $service->server);
        } catch (\Exception $e) {
            \Session::flash('error', __('client.alerts.vpsnotfound'));
            return '';
        }

        if ($vps->hasCorrectTags($service->id) === false) {
            \Session::flash('error', __('client.alerts.vpsnotfound'));
            return '';
        }

        return view('proxmox::panel/reinstallation', [
            'service' => $service,
            'permissions' => $permissions,
            'vps' => $vps,
            'oses' => $vps->getReinstallableOses($service),
            'templates' => $vps->getReinstallableTemplates($service),
            'hostname' => $vps->hostname(),
            'password' => true,
        ]);
    }
}
