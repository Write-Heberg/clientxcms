<?php

namespace App\Modules\Proxmox\Commands;

use App\Modules\Proxmox\Models\ProxmoxConfigModel;

class ProxmoxMigrateConfig extends \Illuminate\Console\Command
{
    protected $signature = 'proxmox:migrate-config';
    protected $description = 'Migrate Proxmox config';

    public function handle()
    {
        $configs = ProxmoxConfigModel::all();
        foreach ($configs as $config) {
            $config = $this->replaceConfig($config);
            $config->save();
        }
        $services = \App\Models\Provisioning\Service::where('type', 'proxmox')->get();
        foreach ($services as $service) {
            $config = $service->getMetadata('config');
            if ($config) {
                $config = $this->replaceConfig($config);
                $service->attachMetadata('config', $config);
                $service->save();
            }
        }
    }

    private function replaceConfig(object $config):object
    {
        if ($config->max_reinstall == 0) {
            $config->max_reinstall = ProxmoxConfigModel::UNLIMITED;
        } else if ($config->max_reinstall == -1) {
            $config->max_reinstall = ProxmoxConfigModel::DISALLOWED;
        }
        if ($config->max_backups == 0) {
            $config->max_backups = ProxmoxConfigModel::UNLIMITED;
        } else if ($config->max_backups == -1) {
            $config->max_backups = ProxmoxConfigModel::DISALLOWED;
        }
        if ($config->max_snapshots == 0) {
            $config->max_snapshots = ProxmoxConfigModel::UNLIMITED;
        } else if ($config->max_snapshots == -1) {
            $config->max_snapshots = ProxmoxConfigModel::DISALLOWED;
        }
    }
}
