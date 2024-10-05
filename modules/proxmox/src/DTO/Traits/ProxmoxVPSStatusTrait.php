<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox\DTO\Traits;

use App\Models\Provisioning\Service;
use App\Modules\Proxmox\Models\ProxmoxLogs;
use App\Modules\Proxmox\ProxmoxAPI;

trait ProxmoxVPSStatusTrait
{
    public function start(Service $service)
    {
        if ($this->isRunning()) {
            return null;
        }
        $user = auth('admin')->check() ? 'admin' : 'user';
        if (\App::runningInConsole())
            $user = 'system';
        try {
            $call = ProxmoxAPI::callApi($this->server, "/nodes/{$this->node}/{$this->type}/{$this->vmid}/status/start", [
                'node' => $this->node,
                'vmid' => $this->vmid
            ], 'POST');
            ProxmoxLogs::addLog($service, ProxmoxLogs::TYPE_START, $user, $this->vmid);
        } catch (\Exception $e) {
            $call = $e->getMessage();
        }
        return $call;
    }

    public function stop(Service $service)
    {
        if ($this->isStopped()) {
            return null;
        }
        $user = auth('admin')->check() ? 'admin' : 'user';
        if (\App::runningInConsole())
            $user = 'system';
        try {
            $call = ProxmoxAPI::callApi($this->server, "/nodes/{$this->node}/{$this->type}/{$this->vmid}/status/stop", [
                'node' => $this->node,
                'vmid' => $this->vmid
            ], 'POST');
            ProxmoxLogs::addLog($service, ProxmoxLogs::TYPE_STOP, $user, $this->vmid);
        } catch (\Exception $e) {
            $call = $e->getMessage();
        }
        return $call;
    }

    public function reboot(Service $service)
    {
        if ($this->isStopped()) {
            return null;
        }
        $user = auth('admin')->check() ? 'admin' : 'user';
        if (\App::runningInConsole())
            $user = 'system';
        try {
            $call = ProxmoxAPI::callApi($this->server, "/nodes/{$this->node}/{$this->type}/{$this->vmid}/status/reboot", [
                'node' => $this->node,
                'vmid' => $this->vmid
            ], 'POST');
            ProxmoxLogs::addLog($service, ProxmoxLogs::TYPE_RESTART, $user, $this->vmid);
        } catch (\Exception $e) {
            $call = $e->getMessage();
        }
        return $call;
    }

    public function isRunning(): bool
    {
        return $this->getResourceUsage()['status'] == 'running';
    }

    public function isStopped(): bool
    {
        return $this->getResourceUsage()['status'] == 'stopped';
    }

    public function status(): string
    {
        return $this->getResourceUsage()['status'];
    }
}
