<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox\Models;

use App\Models\Provisioning\Service;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ProxmoxConfigModel extends Model
{

    const TYPE_LXC = 'lxc';
    const TYPE_QEMU = 'qemu';
    const STORAGE_LOCAL = 'local';
    const UNLIMITED = -1;
    const DISALLOWED = 0;
    protected $table = 'proxmox_configs';
    protected $fillable = [
        'product_id', 'node', 'memory',
        'disk', 'type', 'storage', 'cores',
        'sockets', 'templates', 'oses',
        'max_reinstall', 'max_backups', 'max_snapshots',
        'server_id', 'rate', 'bridge', 'disk_storage',
        'unprivileged', 'features',
    ];
    protected $casts = [
        'templates' => 'array',
        'oses' => 'array',
        'unprivileged' => 'boolean',
    ];

    protected $attributes = [
        'max_reinstall' => -1,
        'max_backups' => -1,
        'max_snapshots' => -1,
        'memory' => 2,
        'disk' => 10,
        'type' => self::TYPE_LXC,
        'cores' => 1,
        'sockets' => 1,
        'rate' => 125,
        'disk_storage' => 'local-lvm',
    ];

    public function toLXCArray(Service $service)
    {
        $data = $service->data;
        if (!array_key_exists('osname', $data)) {
            if (count($this->oses) > 0) {
                $osnames = (array)ProxmoxOS::find(current($this->oses))->osnames;
                $data['osname'] = $osnames[$service->server->id][$this->node] ?? null;
                if ($data['osname'] == null) {
                    throw new \Exception("OS not found for server " . $service->server->id);
                }
            }
        }
        if (!array_key_exists('hostname', $data)) {
            $data['hostname'] = "vps-" . rand(1000, 9999);
        }
        if (!array_key_exists('password', $data)) {
            $data['password'] = \Str::random();
        }
        $service->fill(['data' => $data])->save();
        return $this->cleanNull([
            'cores' => $this->cores,
            "description" => self::formatDescription($service),
            "memory" => $this->memory * 1024,
            "storage" => $service->server->getMetadata('storage_local') ?? self::STORAGE_LOCAL,
            "rootfs" => $this->storage . ":" . $this->disk,
            "features" => $this->features,
            "tags" => "lxc;service-" . $service->id,
            "onboot" => true,
            "searchdomain" => $service->server->getMetadata('searchdomain'),
            "nameserver" => $service->server->getMetadata('nameserver'),
            "hostname" => $data['hostname'],
            "password" => $data['password'],
            "ostemplate" => $data['osname'],
        ]);
    }


    public function toQEMUArray(Service $service, \stdClass $configVPS)
    {
        $disk = explode('G', explode("size=",$configVPS->scsi0)[1])[0];
        $data = $service->data;
        $templateId = $service->data['vmid'];
        $vmid = $service->getMetadata('vmid');
        if (!array_key_exists('password', $data)) {
            $data['password'] = \Str::random();
            $service->fill(['data' => $data])->save();
        }
        if (!array_key_exists('hostname', $data)) {
            $data['hostname'] = "vps-" . rand(1000, 9999);
            $service->fill(['data' => $data])->save();
        }
        return $this->cleanNull([
            'cores' => $this->cores,
            'name' => $data['hostname'],
            "description" => self::formatDescription($service),
            'memory' => $this->memory * 1024,
            'onboot' => true,
            'tags' => "kvm;service-" . $service->id,
            'sockets' => $this->sockets,
            'scsi0' => str_replace($disk, $this->disk, str_replace($templateId, $vmid, $configVPS->scsi0)),
            'cipassword' => $data['password'],
            'searchdomain' => $service->server->getMetadata('searchdomain'),
            'nameserver' => $service->server->getMetadata('nameserver'),
        ]);
    }

    public function toCloneQEMUArray(string $storage, int $vmid, string $node)
    {
        return [
            'newid' => $vmid,
            'full' => 1,
            'storage' => $storage,
            'node' => $node,
            'target' => $node,
        ];
    }

    public static function formatDescription(Service $service, bool $renew = false)
    {
        $array = [
            "Service ID" => $service->id,
            "Service Name" => $service->name,
            "Service Owner" => $service->customer->fullName,
            "Owner Email" => $service->customer->email,
            "Service Expires" => $service->expires_at,
            "Service Created" => $service->created_at,
            "VPS Creation At" => Carbon::now()->format('Y-m-d H:i:s'),
        ];
        if ($service->expires_at == null) {
            unset($array['Service Expires']);
        }
        if ($renew) {
            unset($array['VPS Creation At']);
        }
        return join(" | ", array_map(function ($key, $value) {
            return "$key: $value";
        }, array_keys($array), $array));
    }

    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();
        unset($attributes['id']);
        unset($attributes['created_at']);
        unset($attributes['updated_at']);
        unset($attributes['product_id']);
        $attributes['current_reinstall'] = 0;
        $attributes['current_backups'] = 0;
        $attributes['current_snapshots'] = 0;
        return $attributes;
    }

    private function cleanNull(array $array)
    {
        foreach ($array as $key => $value) {
            if ($value === null) {
                unset($array[$key]);
            }
        }
        return $array;
    }

}
