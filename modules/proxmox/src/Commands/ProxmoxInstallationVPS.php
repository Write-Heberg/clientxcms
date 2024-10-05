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

class ProxmoxInstallationVPS extends Command
{
    protected $signature = 'proxmox:installation-vps';
    protected $description = 'Installation VPS need to be installed.';

    public function handle()
    {
        $services = Service::getItemsByMetadata('proxmox_reinstall', "true");
        $this->info('Running proxmox:installation-vps at ' . now()->format('Y-m-d H:i:s'));
        try {
            foreach ($services as $service){
                $this->info('Installing VPS ' . $service->id);
                $this->install($service);
            }
        } catch (\Exception|ExternalApiException $e) {
            $this->error('Error while installing VPS : ' . $e->getMessage());
        }
    }

    /**
     * @throws ExternalApiException
     */
    private function install(Service $service)
    {
        $time = time();
        $server = $service->server;
        $vmid = $service->getMetadata('vmid');
        $node = $service->getMetadata('node') ?? 'pve';
        $config = json_decode($service->getMetadata('config'), true);
        $config = new ProxmoxConfigModel($config);
        $ips = AddressIPAM::findByService(ProxmoxServerType::getIPAMClass(), $service);
        $rate = $config->rate;
        $bridge = $config->bridge;
        $disk = $config->disk;

        $configVPS = ProxmoxAPI::callApi($server, 'nodes/' . $node . '/qemu/' . $vmid . '/config')->toJson()->data ?? null;
        if ($configVPS == null) {
            throw new ExternalApiException('PROXMOX : No config found for VMID ' . $vmid);
        }
        if (property_exists($configVPS, 'lock')) {
            throw new ExternalApiException('PROXMOX : VMID ' . $vmid . ' is locked');
        }
        $config = $config->toQEMUArray($service, $configVPS);
        $put = [
            'name' => $config['name'],
            'tags' => "kvm;service-" . $service->id,
        ];
        for ($i = 0; $i < count($ips); $i++) {
            $put['net' . $i] = ProxmoxVPSDTO::getQEMUNetwork($ips[$i], $rate, $bridge, $server->getMetadata('network'));
        }
        for ($i = 0; $i < count($ips); $i++) {
            $put['ipconfig' . $i] = ProxmoxVPSDTO::getIPConfig($ips[$i]);
        }
        $config['vmid'] = $vmid;
        self::resizeDisk($server, $node, $vmid, $disk, $configVPS);
        $response = ProxmoxAPI::callApi($server, 'nodes/' . $node . '/qemu/' . $vmid . '/config', $config, "POST");
        $response = ProxmoxAPI::callApi($server, 'nodes/' . $node . '/qemu/' . $vmid . '/config', $put, "PUT");
        $response = ProxmoxAPI::callApi($server, 'nodes/' . $node . '/qemu/' . $vmid . '/status/start', [
            'node' => $node,
            'vmid' => $vmid
        ], "POST");
        $this->info('VPS ' . $vmid . ' has been installed in ' . time() - $time . ' seconds');
        $service->updateMetadata('proxmox_reinstall', "false");
    }

    private static function resizeDisk(Server $server, string $node, int $vmid, int $disk, \stdClass $configVPS)
    {
        $currentDisk = self::getDiskSizeInGB($configVPS->scsi0);
        if ($currentDisk != $disk){
            $params = [
                'disk' => 'scsi0',
                'node' => $node,
                'vmid' => $vmid,
                'size' => "+" . ($disk - $currentDisk) . "G"
            ];
            $response = ProxmoxAPI::callApi($server, 'nodes/' . $node . '/qemu/' . $vmid . '/resize', $params, "PUT");
        }
    }

    private static function getDiskSizeInGB($scsiString) {
        preg_match('/size=([0-9]+)([MG])/', $scsiString, $matches);
        if (empty($matches)) {
            return 'Taille non spécifiée';
        }

        $size = (int)$matches[1];
        $unit = $matches[2];

        if ($unit === 'M') {
            $size = (int)($size / 1024);
        }
        return $size;
    }

}
