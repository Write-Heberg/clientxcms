<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox\Models;
use App\DTO\Provisioning\AddressIPAM;
use App\Models\Provisioning\Server;
use App\Models\Provisioning\Service;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Pagination\Paginator;

class ProxmoxIPAM extends Model implements \App\Contracts\Provisioning\IPAMInterface
{
    protected $table = "proxmox_ipam";

    protected $fillable = [
        'ip',
        'gateway',
        'netmask',
        'bridge',
        'mtu',
        'mac',
        'ipv6',
        'ipv6_gateway',
        'is_primary',
        'service_id',
        'notes',
        'status',
        'server',
    ];

    protected $attributes = [
        'status' => AddressIPAM::AVAILABLE,
        'is_primary' => true,
        'mtu' => 1500,
        'mac' => 'auto',
    ];

    public static function insertIP(AddressIPAM $address): AddressIPAM
    {
        $array = $address->toArray();

        if (str_contains($array['ip'], '/')) {
            $save = $array['ip'];
            $array['ip'] = explode('/', $save)[0];
            $array['netmark'] = explode('/', $save)[1];
        }
        ProxmoxIPAM::create($array);
        return $address;
    }

    public static function updateIP(AddressIPAM $address): AddressIPAM
    {
        ProxmoxIPAM::where('id', $address->id)->update($address->toArray());
        return $address;
    }

    public static function deleteIP(AddressIPAM $address): bool
    {
        return ProxmoxIPAM::where('id', $address->id)->delete();
    }

    public static function findById(int $id): ?AddressIPAM
    {
        $address = ProxmoxIPAM::find($id);
        if ($address) {
            return new AddressIPAM($address->toArray());
        }
        return null;
    }

    public static function findByIP(string $ip): ?AddressIPAM
    {
        if (str_contains($ip, '/')) {
            $ip = explode('/', $ip)[0];
        }
        $address = ProxmoxIPAM::where('ip', $ip)->first();
        if ($address) {
            return new AddressIPAM($address->toArray());
        }
        return null;
    }

    public static function findByService(Service $service): array
    {
        $addresses = ProxmoxIPAM::where('service_id', $service->id)->get();
        $result = [];
        foreach ($addresses as $address) {
            $result[] = new AddressIPAM($address->toArray());
        }
        return $result;
    }

    public static function fetchAdresses(int $nb = 1, ?Server $server = null, ?string $node = null): array
    {
        $serverId = $server?->id ?? null;
        $addresses = ProxmoxIPAM::where('status', AddressIPAM::AVAILABLE)->where('server', $serverId)->orWhereNull('server')->limit($nb)->get();
        $result = [];
        foreach ($addresses as $address) {
            $result[] = new AddressIPAM($address->toArray());
        }
        return $result;
    }

    public static function useAddress(AddressIPAM $address, Service $service): AddressIPAM
    {
        $address->status = AddressIPAM::USED;
        $address->service_id = $service->id;
        ProxmoxIPAM::where('id', $address->id)->update(['status' => AddressIPAM::USED, 'service_id' => $service->id]);
        return $address;
    }

    public static function releaseAddress(AddressIPAM $address): AddressIPAM
    {
        $address->status = AddressIPAM::AVAILABLE;
        $address->service_id = null;
        ProxmoxIPAM::where('id', $address->id)->update(['status' => AddressIPAM::AVAILABLE, 'service_id' => null]);
        return $address;
    }

    public static function fetchAll(): Paginator
    {
        return ProxmoxIPAM::paginate(25);
    }
}
