<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox\DTO\Traits;

use App\DTO\Provisioning\AddressIPAM;
use App\Models\Provisioning\Service;
use App\Modules\Proxmox\DTO\ProxmoxAccountDTO;
use App\Modules\Proxmox\Models\ProxmoxConfigModel;
use App\Modules\Proxmox\Models\ProxmoxLogs;
use App\Modules\Proxmox\Models\ProxmoxOS;
use App\Modules\Proxmox\Models\ProxmoxTemplates;
use App\Modules\Proxmox\ProxmoxAPI;
use App\Modules\Proxmox\ProxmoxServerType;

trait ProxmoxReinstallTrait
{
    public function canReinstall(Service $service): bool
    {
        $maxReinstall = $this->getMaximumReinstall($service);
        $currentReinstall = $this->getCurrentReinstall($service);
        if ($maxReinstall == ProxmoxConfigModel::UNLIMITED) {
            return true;
        }
        if ($maxReinstall == ProxmoxConfigModel::DISALLOWED) {
            return false;
        }
        if ($currentReinstall < $maxReinstall) {
            return true;
        }
        return false;
    }

    public function getMaximumReinstall(Service $service): int
    {
        return $this->getConfigMetadata($service)['max_reinstall'] ?? 0;
    }

    public function getCurrentReinstall(Service $service): int
    {
        return $this->getConfigMetadata($service)['current_reinstall'] ?? 0;
    }

    public function isUnlimitedReinstall(Service $service): bool
    {
        return $this->getMaximumReinstall($service) == ProxmoxConfigModel::UNLIMITED;
    }

    public function hasReinstallLimit(Service $service): bool
    {
        return $this->getMaximumReinstall($service) != ProxmoxConfigModel::UNLIMITED;
    }

    public function markHasDeleted(Service $service)
    {
        $user = auth('admin')->check() ? 'admin' : 'user';
        if (\App::runningInConsole())
            $user = 'system';
        $type = $this->type === 'lxc' ? 'lxc' : 'kvm';
        $call = ProxmoxAPI::callApi($this->server, "/nodes/{$this->node}/{$this->type}/{$this->vmid}/config", [
            'tags' => "$type;service-$service->id;deleted",
            'onboot' => 0
        ], 'PUT');
        try {
            $this->stop($service);
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
        }
        ProxmoxLogs::addLog($service, ProxmoxLogs::TYPE_START_DESTROY, $user, $this->vmid);
    }

    public function reinstall(Service $service, string $password, string $hostname, ?ProxmoxOS $proxmoxOS = null, ?ProxmoxTemplates $proxmoxTemplates = null)
    {
        if (!$this->canReinstall($service)) {
            throw new \Exception('Reinstall limit reached');
        }
        $this->markHasDeleted($service);
        $type = $this->type;
        $user = auth('admin')->check() ? 'admin' : 'user';
        if (\App::runningInConsole())
            $user = 'system';
        $config = new ProxmoxConfigModel($this->getConfigMetadata($service));
        $server = $service->server;
        $data = [
            'password' => $password,
            'hostname' => $hostname,
        ];
        $node = $this->node;
        if ($proxmoxOS !== null) {
            $osnames = (array)($proxmoxOS->osnames);
            $data['osname'] = $osnames[$server->id][$node];
        }
        if ($proxmoxTemplates !== null) {
            $templates = (array)($proxmoxTemplates->vmids);
            $data['vmid'] = $templates[$server->id][$node];
        }
        $service->update(['data' => $data]);
        $ips = AddressIPAM::findByService(ProxmoxServerType::getIPAMClass(), $service);
        $proxmoxAccount = ProxmoxAccountDTO::getUserAccount($service->customer, $server, $service);
        $proxmoxAccount->detachVPS($server, $this->vmid);
        if ($type == ProxmoxConfigModel::TYPE_LXC) {
            $response = ProxmoxAPI::createLXC($server, $config, $service, $ips);
        } else {
            $response = ProxmoxAPI::createQEMU($server, $config, $service, $ips, true);
            $service->updateMetadata('proxmox_reinstall', "true");
        }
        $proxmoxAccount->attachVPS($server, $response->vmid);
        $this->vmid = $response->vmid;
        $service->updateMetadata('vmid', $response->vmid);
        $config['current_reinstall'] = $this->getCurrentReinstall($service) + 1;
        $service->updateMetadata('config', json_encode($config));
        ProxmoxLogs::addLog($service, ProxmoxLogs::TYPE_REINSTALL, $user, $this->vmid);
        $this->start($service);
        return $response;
    }


}
