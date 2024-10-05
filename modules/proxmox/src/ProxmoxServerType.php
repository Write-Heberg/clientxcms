<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox;

use App\Addons\Netbox\NetboxIPAM;
use App\Contracts\Provisioning\IPAMInterface;
use App\DTO\Provisioning\AddressIPAM;
use App\Models\Provisioning\Server;
use App\Models\Provisioning\Service;
use App\Models\Store\Product;
use App\Modules\Proxmox\DTO\ProxmoxAccountDTO;
use App\Modules\Proxmox\Models\ProxmoxConfigModel;
use App\Modules\Proxmox\Models\ProxmoxIPAM;
use GuzzleHttp\Psr7\Response as ResponseGuzzle;

class ProxmoxServerType extends \App\Abstracts\AbstractServerType
{
    protected string $title = 'Proxmox';
    protected string $uuid = 'proxmox';

    public function testConnection(array $params): \App\DTO\Provisioning\ConnectionResponse
    {
        $server = new \App\Models\Provisioning\Server();
        $server->fill($params);
        try {
            $response = ProxmoxAPI::callApi($server, 'nodes')->toGuzzleResponse();
        } catch (\Exception $e) {
            $response = new ResponseGuzzle(500, [], $e->getMessage());
            if ($e instanceof \GuzzleHttp\Exception\RequestException) {
                $response = $e->getResponse();
            }
        }
        return new \App\DTO\Provisioning\ConnectionResponse($response);
    }

    public function createAccount(Service $service): \App\DTO\Provisioning\ServiceStateChangeDTO
    {
        if ($service->type != 'proxmox'){
            return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, false, 'Service type ' . $service->type . ' is not proxmox, cannot create account');
        }
        if ($service->server == null) {
            return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, false, 'No server found for service');
        }
        if ($service->product_id == null) {
            return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, false, 'No product found for service');
        }
        /** @var ProxmoxConfigModel|null $config */
        $config = ProxmoxConfigModel::where('product_id', $service->product_id)->first();
        if ($config === null) {
            return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, false, 'Proxmox config not found for product ' . $service->product_id);
        }
        if ($config->type == ProxmoxConfigModel::TYPE_QEMU && empty($config->templates)) {
            return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, false, 'No templates found for QEMU');
        }
        if ($config->type == ProxmoxConfigModel::TYPE_LXC && empty($config->oses)) {
            return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, false, 'No oses found for LXC');
        }
        $data = $service->data;
        $user = $service->customer;
        $server = $service->server;
        $type = $config->type;
        $count = 1;

        $ips = AddressIPAM::findByService(self::getIPAMClass(), $service);
        if (count($ips) == 0) {
            $ips = AddressIPAM::getAvailableIPs(self::getIPAMClass(), $count, $service->server);
            if (empty($ips))
                return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, false, 'No available IPs');
            AddressIPAM::useIP(self::getIPAMClass(), $ips, $service);
        }
        $proxmoxAccount = ProxmoxAccountDTO::getUserAccount($user, $server, $service);
        if ($type == ProxmoxConfigModel::TYPE_LXC) {
            if ($service->getMetadata('vmid') == null) {
                $response = ProxmoxAPI::createLXC($server, $config, $service, $ips);
            } else {
                $response = new \App\Modules\Proxmox\DTO\ProxmoxVPSDTO($service->getMetadata('vmid'), $type, $config->node, $server);
            }
        } else {
            $response = ProxmoxAPI::createQEMU($server, $config, $service, $ips);
        }
        $service->updateMetadataOrCreate('config', json_encode($config->toArray()));
        $service->updateMetadataOrCreate('vmid', $response->vmid);
        $service->updateMetadataOrCreate('type', $type);
        $service->updateMetadataOrCreate('node', $config->node);
        AddressIPAM::useIP(self::getIPAMClass(), $ips, $service);
        $service->refresh();
        $data = $service->data ?? [];
        $user->notify(new ProxmoxMail($ips, $data, $service->id, $proxmoxAccount));
        $proxmoxAccount->attachVPS($server, $response->vmid);

        return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, true, 'VPS created');
    }

    public function validate(): array
    {
        return [];
    }

    public static function getIPAMClass(): IPAMInterface
    {
        if (class_exists(NetboxIPAM::class)){
            return new NetboxIPAM();
        }
        return new ProxmoxIPAM();
    }

    /**
     * @inheritDoc
     */
    public function onRenew(Service $service): \App\DTO\Provisioning\ServiceStateChangeDTO
    {
        if ($service->type != 'proxmox'){
            return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, false, 'Service type' . $service->type . ' is not proxmox, cannot renew');
        }
        $server = $service->server;
        $vmid = $service->getMetadata('vmid');
        $node = $service->getMetadata('node') ?? 'pve';
        $type = $service->getMetadata('type') ?? 'lxc';
        if ($vmid == null || $node == null || $type == null){
            return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, false, 'VPS not found (please config vmid in service metadata)');
        }
        $type2 = $type == 'lxc' ? 'lxc' : 'qemu';
        $vps = new \App\Modules\Proxmox\DTO\ProxmoxVPSDTO($vmid, $type, $node, $server);
        $call = ProxmoxAPI::callApi($server, "/nodes/{$node}/{$vps->type}/{$vps->vmid}/config", [
            'tags' => "service-{$service->id};$type2",
            'onboot' => 1,
            'description' => ProxmoxConfigModel::formatDescription($service, true)
            ], 'PUT');
        return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, true, 'VPS renewed');
    }

    public function importService(): ProxmoxImport
    {
        return new ProxmoxImport();
    }

    public function findServer(Product $product): ?\App\Models\Provisioning\Server
    {
        $config = ProxmoxConfigModel::where('product_id', $product->id)->first();
        if ($config == null)
            return null;
        return Server::find($config->server_id);
    }

    public function expireAccount(Service $service): \App\DTO\Provisioning\ServiceStateChangeDTO
    {
        if ($service->type != 'proxmox'){
            return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, false, 'Service type' . $service->type . ' is not proxmox, cannot expire');
        }
        $server = $service->server;
        $vmid = $service->getMetadata('vmid');
        $node = $service->getMetadata('node') ?? 'pve';
        $type = $service->getMetadata('type') ?? 'lxc';
        if ($vmid == null){
            return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, true, 'VPS not found, but marked as expired');
        }
        $vps = new \App\Modules\Proxmox\DTO\ProxmoxVPSDTO($vmid, $type, $node, $server);
        $vps->markHasDeleted($service);
        AddressIPAM::releaseIP(self::getIPAMClass(), $service);
        return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, true, 'VPS expired');
    }

    public function suspendAccount(Service $service): \App\DTO\Provisioning\ServiceStateChangeDTO
    {
        if ($service->type != 'proxmox'){
            return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, false, 'Service type' . $service->type . ' is not proxmox, cannot suspend');
        }
        $server = $service->server;
        $vmid = $service->getMetadata('vmid');
        $node = $service->getMetadata('node') ?? 'pve';
        $type = $service->getMetadata('type') ?? 'lxc';

        if ($vmid == null){
            return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, true, 'VPS not found, but marked as suspended');
        }
        $vps = new \App\Modules\Proxmox\DTO\ProxmoxVPSDTO($vmid, $type, $node, $server);
        $vps->stop($service);
        $vps->addTag('suspended');
        return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, true, 'VPS suspended');
    }

    public function unsuspendAccount(Service $service): \App\DTO\Provisioning\ServiceStateChangeDTO
    {
        if ($service->type != 'proxmox'){
            return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, false, 'Service type' . $service->type . ' is not proxmox, cannot unsuspend');
        }
        $server = $service->server;
        $vmid = $service->getMetadata('vmid');
        $node = $service->getMetadata('node') ?? 'pve';
        $type = $service->getMetadata('type') ?? 'lxc';

        if ($vmid == null){
            return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, true, 'VPS not found, but marked as online');
        }
        $vps = new \App\Modules\Proxmox\DTO\ProxmoxVPSDTO($vmid, $type, $node, $server);
        $vps->start($service);
        $vps->removeTag('suspended');
        return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, true, 'VPS unsuspended');
    }


}
