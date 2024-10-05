<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox\DTO\Traits;

use App\Helpers\Date;
use App\Models\Provisioning\Service;
use App\Modules\Proxmox\Models\ProxmoxOS;
use App\Modules\Proxmox\Models\ProxmoxTemplates;
use App\Modules\Proxmox\ProxmoxAPI;

trait ProxmoxConfigTrait
{

    public function getConfig(): array
    {
        if (empty($this->config)) {
            $this->config = (array)ProxmoxAPI::callApi($this->server, "/nodes/{$this->node}/{$this->type}/{$this->vmid}/config")->toArray()['data']->data;
        }
        return $this->config;
    }

    public function getUptime(): string
    {
        $uptime = $this->getResourceUsage()['uptime'];
        if ($uptime === null) {
            return 'Unknown';
        }
        return Date::formatUptime($uptime);
    }

    public function getCores(): int
    {
        if ($this->type === 'lxc') {
            return $this->getConfig()['cores'];
        }
        return $this->getConfig()['sockets'] * $this->getConfig()['cores'];
    }

    public function getOS(): ?string
    {
        if ($this->type === 'lxc')
            return ucfirst($this->getConfig()['ostype']);
        return null;
    }

    public function hostname()
    {
        if ($this->type === 'lxc')
            return $this->getConfig()['hostname'];
        return $this->getConfig()['name'];
    }

    public function colorSize(float $current, float $max): string
    {
        $percent = ($current / $max) * 100;
        if ($percent < 50) {
            return 'text-gray-700 dark:text-gray-400';
        }
        if ($percent < 80) {
            return 'text-warning';
        }
        return 'text-red-500 dark:text-red-400';
    }

    public function getConfigMetadata(Service $service)
    {
        if (empty($this->configMetada)) {
            $this->configMetada = json_decode($service->getMetadata('config'), true);
        }
        return $this->configMetada;
    }

    public function getReinstallableOses(Service $service)
    {
        $oses = $this->getConfigMetadata($service)['oses'] ?? [];
        return ProxmoxOS::whereIn('id', $oses)->get();
    }

    public function getReinstallableTemplates(Service $service)
    {
        $templates = $this->getConfigMetadata($service)['templates'] ?? [];
        return ProxmoxTemplates::whereIn('id', $templates)->get();
    }
}
