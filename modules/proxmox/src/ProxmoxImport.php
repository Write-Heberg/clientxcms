<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox;

use App\Contracts\Provisioning\ImportServiceInterface;
use App\DTO\Provisioning\ServiceStateChangeDTO;
use App\Models\Provisioning\Server;
use App\Models\Provisioning\Service;
use App\Modules\Proxmox\DTO\ProxmoxAccountDTO;
use App\Modules\Proxmox\DTO\ProxmoxVPSDTO;
use App\Modules\Proxmox\Models\ProxmoxConfigModel;
use App\Modules\Proxmox\Models\ProxmoxOS;
use App\Modules\Proxmox\Models\ProxmoxTemplates;

class ProxmoxImport implements ImportServiceInterface
{

    public function import(Service $service, array $data = []): ServiceStateChangeDTO
    {
        [$node, $vmid, $type, $server_id] = explode('-', $data['vmid']);
        $server = Server::find($server_id);
        $type2 = $type == 'lxc' ? 'lxc' : 'kvm';
        if ($service->product != null){
            $config = ProxmoxConfigModel::where('product_id', $service->product->id)->first();
        } else {
            $configApi = ProxmoxAPI::callApi($server, "/nodes/{$node}/{$type}/{$vmid}/config")->toJson()->data;
            $config = new ProxmoxConfigModel(collect($configApi)->only([
                'memory', 'disk', 'cores', 'sockets', 'storage', 'max_reinstall', 'max_backups', 'max_snapshots', 'rate'
            ])->toArray());
        }
        $service->attachMetadata('vmid', $vmid);
        $service->attachMetadata('type', $type);
        $service->attachMetadata('node', $node);
        $service->attachMetadata('config', $config);
        $service->update(['server_id' => $server->id]);
        $proxmoxAccount = ProxmoxAccountDTO::getUserAccount($service->customer, $server, $service);
        $proxmoxAccount->attachVPS($server, $vmid);
        $call = ProxmoxAPI::callApi($server, "/nodes/{$node}/{$type}/{$vmid}/config", [
            'tags' => "service-{$service->id};$type2",
            'onboot' => 1,
            'description' => ProxmoxConfigModel::formatDescription($service)
        ], 'PUT');
        try {
            (new ProxmoxVPSDTO($vmid, $type, $node, $server))->savePrimaryIp($service->id);
        } catch (\Exception $e) {
            \Log::error('Error while saving primary IP: ' . $e->getMessage());
        }
        return new ServiceStateChangeDTO($service, true, 'VPS imported');
    }

    public function validate(): array
    {
        return [
            'vmid' => 'required',
            'memory' => 'required|numeric|min:0.1',
            'disk' => 'required|numeric|min:0.1',
            'cores' => 'required|numeric|min:1',
            'sockets' => 'required|numeric|min:1',
            'storage' => 'required|string',
            'max_reinstall' => 'required|numeric|min:-1',
            'max_backups' => 'required|numeric|min:-1',
            'max_snapshots' => 'required|numeric|min:-1',
            'oses' => 'array|nullable',
            'templates' => 'array|nullable',
            'rate' => 'required|numeric|min:0.0125',
            'server_id' => 'required|numeric|exists:servers,id',
        ];
    }

    public function render(Service $service, array $data = [])
    {
        $servers = Server::where('type', 'proxmox')->where('status', 'active')->get();
        $vps = [];
        $storages = [];
        $config = new ProxmoxConfigModel();
        $templates = ProxmoxTemplates::all()->pluck('name', 'id');
        $oses = ProxmoxOS::all()->pluck('name', 'id');
        $rates = [
            "0.0125" => '100 MB/s',
            "31.25" => '250 MB/s',
            "62.5" => '500 MB/s',
            "125" => '1 GB/s',
            "1250" => '10 GB/s',
        ];
        foreach ($servers as $server) {
            try {
                $vms = ProxmoxAPI::callApi($server, 'cluster/resources', ['type' => 'vm'])->toJson()->data;
            } catch (\Exception $e) {
                \Session::flash('error', 'Error while fetching VMs from ' . $server->name . ' : ' . $e->getMessage());
                $vms = [];
            }
            foreach ($vms as $vm) {
                $tags = explode(';', $vm->tags ?? '') ?? [];
                if (!in_array('lxc', $tags) && !in_array('qemu', $tags)) {
                    $key = $vm->node . '-' . $vm->vmid  . '-' . $vm->type . '-' . $server->id;
                    $vps[$key] = $vm->name . ' (' . $vm->vmid . ')' . ' - ' . $server->name . ' - ' . $vm->node;
                }
            }
            foreach (ProxmoxAPI::fetchStorages($server) as $storage) {
                foreach ($storage as $_storage) {
                    $storages[$_storage] = $_storage;
                }
            }
        }
        return view('proxmox_admin::import', compact('rates', 'templates', 'oses', 'service','config', 'data', 'vps', 'storages'));
    }
}
