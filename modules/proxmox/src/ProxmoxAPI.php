<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox;

use App\DTO\Provisioning\AddressIPAM;
use App\Exceptions\ExternalApiException;
use App\Models\Provisioning\Server;
use App\Models\Provisioning\Service;
use App\Modules\Proxmox\DTO\ProxmoxVPSDTO;
use App\Modules\Proxmox\Models\ProxmoxTemplates;

class ProxmoxAPI
{
    public static function callApi(Server $server, string $endpoint, array $data = [], string $method = "GET")
    {
        $url = "https://". $server->address . ':' . $server->port;
        $url .= "/api2/json/" . $endpoint;
        $method = strtolower($method);
        $response = \Http::withHeaders([
            'Authorization' => 'PVEAPIToken=' . $server->username . '=' . $server->password,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json'
        ])->withoutVerifying()->timeout(20)->$method($url, $data);
        if ($response->failed()) {
            if ($response->unauthorized() || $response->forbidden()) {
                throw new \RuntimeException(sprintf('Unauthorized or forbidden access to %s', $url));
            }
            if ($response->serverError()) {
                throw new ExternalApiException(sprintf('Server error while accessing %s : %s (data : %s)', $url, $response->body(), json_encode($data)));
            }
            if ($response->badRequest()) {
                throw new ExternalApiException(sprintf('Bad request while accessing %s : %s returned Value : %s', $url, $method, self::formatBody($response->body())));
            }
        }
        return new ProxmoxResponse($response);
    }

    public static function fetchBridges(Server $server, string $node)
    {
        $response = self::callApi($server, "nodes/$node/network")->toJson();
        $networks = [];
        foreach ($response->data as $network) {
            $networks[$network->iface] = $network->iface;
        }
        return $networks;
    }

    public static function fetchTemplates(Server $server, bool $force = false)
    {
        if ($force && \Cache::get('templates_' . $server->id) !== null) {
            \Cache::forget('templates_' . $server->id);
        }
        if (!$force && \Cache::get('templates_' . $server->id) !== null) {
            return \Cache::get('templates_' . $server->id);
        }
        $vms = self::callApi($server, 'cluster/resources')->toJson();
        $templates = [];
        if ($vms == null) {
            return [];
        }
        foreach ($vms->data as $vm) {
            if (property_exists($vm, 'template') && $vm->template == 1) {
                $templates[$vm->node . '-' . $vm->vmid] = $vm->name . ' (' . $vm->vmid . ')' . ' (' . $vm->node . ')';
            }
        }
        return $templates;
    }

    public static function fetchOses(Server $server, bool $force = false)
    {
        if ($force && \Cache::get('oses_' . $server->id) !== null) {
            \Cache::forget('oses_' . $server->id);
        }
        if (!$force && \Cache::get('oses_' . $server->id) !== null) {
            return \Cache::get('oses_' . $server->id);
        }
        $oses = [];
        $storages = self::fetchStorages($server);
        foreach ($storages as $node => $_storages) {
            foreach ($_storages as $storage) {
                $response = self::callApi($server, 'nodes/' . $node . '/storage/' . $storage . '/content')->toJson();
                foreach ($response->data as $os) {
                    if ($os->content  == 'iso' || $os->content  == 'vztmpl') {
                        $oses[$node . '----' . $os->volid] = $os->volid . ' (' . $os->content . ') (' . $node . ')';
                    }
                }
            }
        }
        \Cache::put('oses_' . $server->id, $oses, now()->addDays(7));
        return $oses;
    }

    public static function fetchNodes(Server $server, bool $array = true)
    {
        $response = self::callApi($server, 'nodes')->toJson();
        if ($response == null) {
            return [];
        }
        $nodes = [];
        if ($array) {
            for ($i = 0; $i < count($response->data); $i++) {
                $nodes[$response->data[$i]->node] = $response->data[$i]->node;
            }
            return $nodes;
        }
        return $response;
    }

    public static function fetchStorages(Server $server, bool $force = false):array
    {
        if ($force && \Cache::get('storages_' . $server->id) !== null) {
            \Cache::forget('storages_' . $server->id);
        }
        if (!$force && \Cache::get('storages_' . $server->id) !== null) {
            return \Cache::get('storages_' . $server->id);
        }
        $storages = [];
        $nodes = self::fetchNodes($server, false);
        foreach ($nodes->data as $node) {
            $response = self::callApi($server, 'nodes/' . $node->node . '/storage')->toJson();
            foreach ($response->data as $storage) {
                if ($storage->active)
                    $storages[$node->node][$storage->storage] = $storage->storage;
            }
        }
        \Cache::put('storages_' . $server->id, $storages, now()->addDays(7));
        return $storages;
    }

    /**
     * @param Server $server
     * @param Models\ProxmoxConfigModel $config
     * @param AddressIPAM[] $ips
     * @return ProxmoxVPSDTO
     */
    public static function createLXC(Server $server, Models\ProxmoxConfigModel $config, Service $service, array $ips = []): ProxmoxVPSDTO
    {
        $node = $config->node;
        $rate = $config->rate;
        $vmid = self::callApi($server, 'cluster/nextid')->toJson()->data;
        $bridge = $config->bridge;
        $config = $config->toLXCArray($service);
        for ($i = 0; $i < count($ips); $i++) {
            $config['net' . $i] = ProxmoxVPSDTO::getLXCNetwork($ips[$i], $i, $rate, $bridge, $server->getMetadata('network'));
        }
        $config['vmid'] = $vmid;
        $response = self::callApi($server, 'nodes/' . $node . '/lxc', $config, "POST");
        if (!$response->successful()) {
            throw new ExternalApiException('PROXMOX : Error while creating LXC : ' . $response->toJson());
        }
        $response = ProxmoxAPI::callApi($server, 'nodes/' . $node . '/lxc/' . $vmid . '/status/start', [
            'node' => $node,
            'vmid' => $vmid
        ], "POST");
        return new ProxmoxVPSDTO($vmid, "lxc", $node, $server);
    }

    public static function createQEMU(Server $server, ?Models\ProxmoxConfigModel $config, Service $service, array $ips, bool $forceClone = false)
    {
        $node = $config->node;
        $storage = $config->storage;
        if ($service->getMetadata('vmid') != null && !$forceClone) {
            $vmid = $service->getMetadata('vmid');
        } else {
            if (array_key_exists('vmid', $service->data) && $service->data['vmid'] != null) {
                $templateId = $service->data['vmid'];
            } else {
                if (ProxmoxTemplates::first() == null)
                    throw new \RuntimeException('No default template found');
                $vmids = (array) ProxmoxTemplates::first()->vmids;
                $templateId = $vmids[$config->server_id][$config->node] ?? null;
                if ($templateId == null)
                    throw new \RuntimeException(sprintf('No default template found for %s : %s', $service->server_id, $config->node));
            }
            $vmid = self::callApi($server, 'cluster/nextid')->toJson()->data;
            $clone = self::callApi($server, 'nodes/' . $node . '/qemu/' . $templateId . '/clone', $config->toCloneQEMUArray($storage, $vmid, $node), "POST");
            if (!$clone->successful()) {
                throw new ExternalApiException('PROXMOX : Error while cloning VMID ' . $templateId . ' : ' . $clone->toJson());
            }
            $service->attachMetadata('vmid', $vmid);
            $service->attachMetadata('proxmox_reinstall', "true");
        }
        return new ProxmoxVPSDTO($vmid, "qemu", $node, $server);
    }

    public static function formatBody(string $body)
    {
        if (json_decode($body) !== null) {
            $body = json_decode($body);
            if (property_exists($body, 'errors')) {
                return collect($body->errors)->map(function ($k, $v) {
                    return $k . ' : ' . $v;
                })->implode("\n");
            }
            if (property_exists($body, 'data')) {
                if ($body->data == NULL) {
                    return 'No data';
                }
            }
        }
        return $body;
    }

}
