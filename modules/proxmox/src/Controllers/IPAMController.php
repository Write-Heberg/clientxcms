<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox\Controllers;

use App\DTO\Admin\MassActionDTO;
use App\DTO\Provisioning\AddressIPAM;
use App\Events\Resources\ResourceUpdatedEvent;
use App\Http\Controllers\Admin\AbstractCrudController;
use App\Models\Core\Permission;
use App\Models\Provisioning\Server;
use App\Models\Provisioning\Service;
use App\Modules\Proxmox\Models\ProxmoxIPAM;
use App\Modules\Proxmox\Models\ProxmoxLogs;
use App\Modules\Proxmox\ProxmoxAPI;
use App\Modules\Proxmox\Requests\IPAMRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class IPAMController extends AbstractCrudController
{
    protected string $viewPath = 'proxmox_admin::ipam';
    protected string $routePath = 'admin.proxmox.ipam';
    protected string $model = ProxmoxIPAM::class;
    protected string $translatePrefix = 'proxmox::messages.ipam';
    protected bool $extensionPermission = true;

    public function getIndexFilters()
    {
        return [
            AddressIPAM::AVAILABLE => __('proxmox::messages.ipam.states.available.title'),
            AddressIPAM::USED => __('proxmox::messages.ipam.states.used.title'),
            AddressIPAM::UNAVAILABLE => __('proxmox::messages.ipam.states.unavailable.title'),
        ];
    }

    public function getSearchFields()
    {
        return [
            'ip' => __('proxmox::messages.ipam.ip'),
            'gateway' => __('proxmox::messages.ipam.gateway'),
            'netmask' => __('proxmox::messages.ipam.netmask'),
            'bridge' => __('proxmox::messages.ipam.bridge'),
            'mtu' => __('proxmox::messages.ipam.mtu'),
            'vmid' => 'VM ID',
            'service_id' => 'Service ID'
        ];
    }

    public function getMassActions()
    {
        return [
            'delete' => new MassActionDTO('delete', __('global.delete'), function (ProxmoxIPAM $item) {
                $item->delete();
            }),
            'set_available' => new MassActionDTO('set_available', __('proxmox::messages.ipam.states.available.mass_action'), function (ProxmoxIPAM $item) {
                $item::releaseAddress(new AddressIPAM($item->toArray()));
            }),
            'set_used' => new MassActionDTO('set_used', __('proxmox::messages.ipam.states.used.mass_action'), function (ProxmoxIPAM $item, ?string $serviceId) {
                $service = $serviceId ? Service::find($serviceId) : null;
                if ($service) {
                    $item::useAddress(new AddressIPAM($item->toArray()), $service);
                } else {
                    $item->update(['status' => AddressIPAM::USED]);
                }
            }),
            'set_unavailable' => new MassActionDTO('set_unavailable', __('proxmox::messages.ipam.states.unavailable.mass_action'), function (ProxmoxIPAM $item) {
                $item->update(['status' => AddressIPAM::UNAVAILABLE]);
            }),
        ];
    }

    public function store(IPAMRequest $request)
    {
        $address = ProxmoxIPAM::create($request->validated());
        return $this->storeRedirect($address);
    }

    public function update(ProxmoxIPAM $ipam, IPAMRequest $request)
    {
        $ipam->update($request->validated());
        return $this->updateRedirect($ipam);
    }

    public function show(ProxmoxIPAM $ipam)
    {
        return $this->showView(['item' => $ipam, 'bridges' => $this->getBridges(), 'servers' => $this->getServers()]);
    }

    public function destroy(ProxmoxIPAM $ipam)
    {
        $ipam->delete();
        return $this->deleteRedirect($ipam);
    }

    protected function storeRedirect(Model $model)
    {
        event(new ResourceUpdatedEvent($model));
        return back()->with('success', __($this->flashs['created']));
    }

    protected function getCreateParams()
    {
        return array_merge(parent::getCreateParams(), ['bridges' => $this->getBridges(), 'servers' => $this->getServers()]);
    }

    public function ranges(Request $request)
    {
        $validated = $request->validate([
            'range' => 'required|string',
            'block' => 'required|string',
            'range_mask' => 'required|integer|min:1|max:32',
            'range_gateway' => 'required|ip',
            'range_bridge' => 'nullable|string',
            'range_mtu' => 'nullable|integer',
            'range_server' => 'required|integer',
        ], $request->all());
        [$min, $max] = explode('-', $validated['range']);
        for ($i = $min; $i <= $max; $i++) {
            ProxmoxIPAM::create([
                'ip' => str_replace('.XX', '.' . $i, $validated['block']),
                'gateway' => $validated['range_gateway'],
                'netmask' => $validated['range_mask'],
                'bridge' => $validated['range_bridge'],
                'mtu' => $validated['range_mtu'],
                'status' => AddressIPAM::AVAILABLE,
                'server' => $validated['range_server'] == 'none' ? null : $validated['range_server'],
            ]);
        }
        return redirect()->route($this->routePath . '.index')->with('success', __('proxmox::messages.ipam.create.ranges.success', ['count' => $max - $min + 1]));
    }

    public function logs()
    {
        staff_aborts_permission(Permission::MANAGE_SETTINGS);
        $logs = ProxmoxLogs::paginate(100);
        $translatePrefix = 'proxmox::messages.logs';
        return view('proxmox_admin::logs', compact('logs','translatePrefix'));
    }

    private function getBridges()
    {
        $bridges = [];
        $servers = Server::getAvailable(true)->where('type', 'proxmox')->get();
        foreach ($servers as $server) {
            foreach (ProxmoxAPI::fetchNodes($server) as $node) {
                foreach (ProxmoxAPI::fetchBridges($server, $node) as $bridge) {
                    $bridges[$bridge] = $bridge;
                }
            }
        }
        return $bridges;
    }

    private function getServers()
    {
        $servers = Server::getAvailable(true)->where('type', 'proxmox')->get();
        $tmp = [];
        foreach ($servers as $server) {
            $tmp[$server->id] = $server->name;
        }
        $tmp['none'] = __('global.any');
        return $tmp;
    }

}
