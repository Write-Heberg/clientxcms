<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox\DTO;

use App\Models\Provisioning\Server;
use App\Models\Provisioning\Service;
use App\Modules\Proxmox\Models\ProxmoxConfigModel;
use App\Modules\Proxmox\Models\ProxmoxLogs;
use App\Modules\Proxmox\ProxmoxAPI;

class ProxmoxVPSDTO
{
    public int $vmid;
    public string $type;
    private string $node;
    private Server $server;

    private array $resources = [];
    private array $config = [];
    private array $rdddata = [];
    private array $configMetada = [];
    public ?string $timeframe = 'hour';

    use Traits\ProxmoxVPSStatusTrait;
    use Traits\ProxmoxConfigTrait;
    use Traits\ProxmoxNetworkTrait;
    use Traits\ProxmoxRdddataTrait;
    use Traits\ProxmoxReinstallTrait;

    public function __construct(int $vmid, string $type, string $node, Server $server)
    {
        $this->vmid = $vmid;
        $this->type = $type;
        $this->node = $node;
        $this->server = $server;
    }


    public function getResourceUsage(): array
    {
        if (empty($this->resources)) {
            $this->resources = (array)ProxmoxAPI::callApi($this->server, "/nodes/{$this->node}/{$this->type}/{$this->vmid}/status/current")->toArray()['data']->data;
        }
        return $this->resources;
    }

    public function hasCorrectTags(int $serviceId)
    {
        $tags = $this->getResourceUsage()['tags'] ?? '';
        $tags = explode(';', $tags);
        return collect($tags)->filter(function ($tag) use ($serviceId) {
            if (!\Str::startsWith($tag, 'service-')) {
                return false;
            }
            return \Str::startsWith($tag, 'service-' . $serviceId);
        })->isNotEmpty();
    }

    public function deleteVPS(Service $service)
    {
        if ($this->isStopped() === false) {
            $this->stop($service);
        }
        $user = auth('admin')->check() ? 'admin' : 'user';
        if (\App::runningInConsole())
            $user = 'system';
        ProxmoxAPI::callApi($this->server, 'nodes/' . $this->node . '/'. $this->type .'/' . $this->vmid, [], 'DELETE');
        ProxmoxLogs::addLog($service, ProxmoxLogs::TYPE_DESTROY, $user, $this->vmid);
        $this->deleteSnapshot($service);
        $this->deleteBackup($service);
    }

    public function addTag(string $string)
    {
        $tags = $this->getResourceUsage()['tags'] ?? '';
        $tags = explode(';', $tags);
        $tags[] = $string;
        $tags = array_unique($tags);
        $tags = implode(';', $tags);
        ProxmoxAPI::callApi($this->server, "/nodes/{$this->node}/{$this->type}/{$this->vmid}/config", [
            'tags' => $tags,
        ], 'PUT');
    }

    public function removeTag(string $string)
    {
        $tags = $this->getResourceUsage()['tags'] ?? '';
        $tags = explode(';', $tags);
        $tags = collect($tags)->filter(function ($tag) use ($string) {
            return $tag != $string;
        })->toArray();
        $tags = implode(';', $tags);
        ProxmoxAPI::callApi($this->server, "/nodes/{$this->node}/{$this->type}/{$this->vmid}/config", [
            'tags' => $tags,
        ], 'PUT');
    }

    private function deleteSnapshot(Service $service)
    {
        $snapshots = ProxmoxAPI::callApi($this->server, "/nodes/{$this->node}/{$this->type}/{$this->vmid}/snapshot")->toArray()['data'];
        foreach ($snapshots as $snapshot) {
            ProxmoxAPI::callApi($this->server, "/nodes/{$this->node}/{$this->type}/{$this->vmid}/snapshot/{$snapshot->name}", [], 'DELETE');
            ProxmoxLogs::addLog($service, ProxmoxLogs::TYPE_SNAPSHOT_DESTROY, 'system', $this->vmid, $snapshot->name);
        }
    }

    private function deleteBackup(Service $service)
    {
        /** @var Server $server */
        $server = $service->server;
        if (!$server){
            return;
        }
        if ($server->type != 'proxmox' || $server->hasMetadata('proxmox_backup_storage')){
            return;
        }
        $storage = $server->getMetadata('proxmox_backup_storage');
        $node = $server->getMetadata('proxmox_backup_node');
        $vmid = $this->vmid;
        $backups = ProxmoxAPI::callApi($server, "/nodes/$node/storage/$storage/content?content=backup&vmid=$vmid")->toArray()['data'];
        foreach ($backups as $backup) {
            ProxmoxAPI::callApi($server, "/nodes/$node/storage/$storage/content/$backup->volid", [], 'DELETE');
            ProxmoxLogs::addLog($service, ProxmoxLogs::TYPE_BACKUP_DESTROY, 'system', $this->vmid);
        }
    }


}
