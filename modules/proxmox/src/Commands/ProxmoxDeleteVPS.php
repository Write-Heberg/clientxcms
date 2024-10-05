<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox\Commands;

use App\Models\Provisioning\Service;
use App\Modules\Proxmox\ProxmoxAPI;
use Illuminate\Console\Command;

class ProxmoxDeleteVPS extends Command
{
    protected $signature = 'proxmox:delete-vps';
    protected $description = 'Delete VPS has "deleted" tag from Proxmox server.';

    public function handle()
    {

        $servers = \App\Models\Provisioning\Server::where('type', 'proxmox')->where('status', 'active')->get();
        $this->info('Running proxmox:delete-vps at ' . now()->format('Y-m-d H:i:s'));
        foreach ($servers as $server) {
            try {
                $vms = ProxmoxAPI::callApi($server, 'cluster/resources', ['type' => 'vm'])->toJson()->data;
            } catch (\Exception $e) {
                $this->error('Error while fetching VMs from ' . $server->name . ' : ' . $e->getMessage());
                continue;
            }
            foreach ($vms as $vm) {
                if (property_exists($vm, 'tags') && in_array('deleted', explode(';', $vm->tags ?? '')) && !in_array('disable_deletion', explode(';', $vm->tags ?? ''))) {
                    try {
                        sleep(5);
                        ProxmoxAPI::callApi($server, 'nodes/' . $vm->node . '/'. $vm->type .'/' . $vm->vmid, [], 'DELETE');
                        $this->info('VPS ' . $vm->name . ' (' . $vm->vmid . ') deleted from ' . $server->name);
                    } catch (\Exception $e) {
                        $this->error('Error while deleting VPS ' . $vm->name . ' (' . $vm->vmid . ') from ' . $server->name . ' : ' . $e->getMessage());
                    }
                }
            }
        }
    }
}
