<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox\DTO\Traits;

use App\DTO\Provisioning\AddressIPAM;
use App\Modules\Proxmox\ProxmoxServerType;

trait ProxmoxNetworkTrait
{
    public static function getLXCNetwork(AddressIPAM $ipam, int $i, float $rate, string $bridge, ?string $additional=null):string
    {
        $values['ip'] = $ipam->ip . '/' . $ipam->netmask;
        $values['gw'] = $ipam->gateway;
        $values['bridge'] = $bridge != $ipam->bridge ? $bridge : $ipam->bridge;
        $values['name'] = 'eth' . $i;
        if ($ipam->mac !== null && $ipam->mac !== 'auto')
            $values['hwaddr'] = $ipam->mac;
        if ($additional !== null){
            foreach (explode(',', $additional) as $add){
                [$key, $value] = explode('=', $add);
                $values[$key] = $value;
            }
        }
        $values['rate'] = $rate;
        return join(',', array_map(function ($v, $k) {
            return $k . '=' . $v;
        }, $values, array_keys($values)));
    }


    public static function getQEMUNetwork(AddressIPAM $ipam, float $rate,string $bridge, ?string $additional=null):string
    {
        if ($ipam->mac !== null && $ipam->mac !== 'auto')
            $values['macaddr'] = $ipam->mac;
        $values['bridge'] = $bridge != $ipam->bridge ? $bridge : $ipam->bridge;
        $values['rate'] = $rate;
        if ($additional !== null){
            foreach (explode(',', $additional) as $add){
                [$key, $value] = explode('=', $add);
                $values[$key] = $value;
            }
        }
        return "virtio," . join(',', array_map(function ($v, $k) {
                return $k . '=' . $v;
            }, $values, array_keys($values)));
    }

    public static function getIPConfig(AddressIPAM $ipam):string
    {
        $values['ip'] = $ipam->ip . '/' . $ipam->netmask;
        $values['gw'] = $ipam->gateway;
        if ($ipam->ipv6_gateway !== null)
            $values['gw6'] = $ipam->ipv6_gateway;
        if ($ipam->ipv6 !== null)
            $values['ip6'] = $ipam->ipv6;

        return join(',', array_map(function ($v, $k) {
            return $k . '=' . $v;
        }, $values, array_keys($values)));
    }

    public function getBandwidth(): string
    {
        $net0 = $this->getConfig()['net0'];
        $net0 = explode(',', $net0);
        $rate = collect($net0)->filter(function ($value) {
            return \Str::startsWith($value, 'rate=');
        })->first();
        if ($rate === null) {
            return 'Unknown';
        }
        $bwlimit = explode('=', $rate)[1];
        $mbps = $bwlimit * 8;
        $unit = $mbps >= 1000 ? " GB/s" : " MB/s";
        if ($mbps < 1000){
            $mbps = $mbps * 1000;
        }
        if ($mbps <= 100){
            $mbps = $mbps * 1000;
        }
        return (int)($mbps / 1000) . $unit;
    }


    public function getPrimaryIp(): AddressIPAM
    {
        $ip = null;
        $config = $this->getConfig();
        if (array_key_exists('ipconfig0', $config)) {

            $ipconfig = explode(',', $config['ipconfig0']);
            $ip = collect($ipconfig)->mapWithKeys(function ($value) {
                [$key, $value] = explode('=', $value);
                return [$key => $value];
            })->filter(function ($value, $key) {
                return $key == 'ip';
            })->first();
        }
        if (array_key_exists('net0', $config)) {
            $net0 = explode(',', $config['net0']);
            if ($ip === null) {
                $ip = collect($net0)->mapWithKeys(function ($value) {
                    [$key, $value] = explode('=', $value);
                    return [$key => $value];
                })->filter(function ($value, $key) {
                    return $key === 'ip';
                })->first();
            }
        }
        if ($ip === null) {
            throw new \Exception(sprintf("No primary IP found for VMID %s", $this->vmid));
        }
        $ip = ProxmoxServerType::getIPAMClass()::findByIP($ip);
        if ($ip === null) {
            throw new \Exception(sprintf("No primary IP found for VMID %s", $this->vmid));
        }
        return $ip;
    }

    public function savePrimaryIp(int $serviceId): AddressIPAM
    {
        try {
            $ip = $this->getPrimaryIp();
            if ($ip->service_id !== $serviceId) {
                $ip->service_id = $serviceId;
                ProxmoxServerType::getIPAMClass()::updateIP($ip);
            }
        } catch (\Exception $e) {
            //$ip = $this->createPrimaryIp($serviceId);
        }
        return $ip;
    }

    private function createPrimaryIp(int $serviceId): AddressIPAM
    {
        $ip = null;
        $config = $this->getConfig();
        if ($this->type == 'qemu') {
            $ipconfig = null;
            $net = null;
            if (array_key_exists('ipconfig0', $config)) {
                $ipconfig0 = explode(',', $config['ipconfig0']);
                $ipconfig = collect($ipconfig0)->mapWithKeys(function ($value) {
                    [$key, $value] = explode('=', $value);
                    return [$key => $value];
                })->toArray();
            }
            if (array_key_exists('net0', $config)) {
                $net0 = explode(',', $config['net0']);
                $net = collect($net0)->mapWithKeys(function ($value) {
                    [$key, $value] = explode('=', $value);
                    return [$key => $value];
                })->toArray();
            }
            return AddressIPAM::fromQemuVM($net, $ipconfig, $serviceId);
        } else {
            $net0 = explode(',', $config['net0']);
            $net = collect($net0)->mapWithKeys(function ($value) {
                [$key, $value] = explode('=', $value);
                return [$key => $value];
            })->toArray();
            return AddressIPAM::fromLxcVM($net, $serviceId);
        }

        return ProxmoxServerType::getIPAMClass()::findByIP($ip);
    }
}
