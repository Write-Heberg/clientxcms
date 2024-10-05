<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox\Commands;

use App\DTO\Provisioning\AddressIPAM;
use App\Exceptions\ExternalApiException;
use App\Models\Provisioning\Server;
use App\Models\Provisioning\Service;
use App\Modules\Proxmox\DTO\ProxmoxVPSDTO;
use App\Modules\Proxmox\Models\ProxmoxConfigModel;
use App\Modules\Proxmox\ProxmoxAPI;
use App\Modules\Proxmox\ProxmoxServerType;
use Illuminate\Console\Command;

class ProxmoxDiskVPS extends Command
{
    protected $signature = 'proxmox:disk-vps';
    protected $description = 'Installation VPS need to be installed.';

    public function handle()
    {
        $this->info('Running proxmox:disk-vps at ' . now()->format('Y-m-d H:i:s'));
        \Cache::clear();
        $servers = Server::where('type', 'proxmox')->get();
        if ($servers->isEmpty()) {
            $this->error('No proxmox server found');
            return;
        }
        try {
           foreach ($servers as $server) {
               $storages = ProxmoxAPI::fetchStorages($server, true);
               $oses = ProxmoxAPI::fetchOses($server, true);
               $templates = ProxmoxAPI::fetchTemplates($server, true);
               foreach ($storages as $node => $_storages) {
                   $bridges = ProxmoxAPI::fetchBridges($server, $node);
                   dump("Node: $node");
                   foreach ($_storages as $storage) {
                       dump("Storage: $storage");
                   }
                     foreach ($bridges as $bridge) {
                          dump("Bridge: $bridge");
                     }
                     if (empty($bridges)) {
                         $this->error('No bridge found for server ' . $server->name);
                     }
               }
               if (empty($storages)) {
                   $this->error('No storage found for server ' . $server->name);
               }
               if (empty($templates) || empty($oses)) {
                   $this->error('No templates or OS found for server ' . $server->name);
               }
               foreach ($oses as $os) {
                   dump("OS: $os");
               }
                foreach ($templates as $template) {
                     dump("Template: $template");
                }

            }
            $this->info('proxmox:disk-vps finished at ' . now()->format('Y-m-d H:i:s'));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
