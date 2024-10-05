<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox\Models;

use App\Models\Provisioning\Service;
use Illuminate\Database\Eloquent\Model;

class ProxmoxLogs extends Model
{
    protected $table = 'proxmox_logs';
    protected $fillable = ['type', 'user', 'vmid', 'service_id', 'created_at'];
    public $timestamps = false;
    protected $dates = ['created_at'];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    const TYPE_START = 'proxmox::messages.logs.start';
    const TYPE_STOP = 'proxmox::messages.logs.stop';
    const TYPE_RESTART = 'proxmox::messages.logs.restart';
    const TYPE_DESTROY = 'proxmox::messages.logs.destroy';
    const TYPE_REINSTALL = 'proxmox::messages.logs.reinstall';
    const TYPE_REINSTALL_DONE = 'proxmox::messages.logs.reinstall_done';
    const TYPE_START_DESTROY = 'proxmox::messages.logs.start_destroy';
    const TYPE_SNAPSHOT_DESTROY = 'proxmox::messages.logs.snapshot_destroy';
    const TYPE_SNAPSHOT_CREATE = 'proxmox::messages.logs.snapshot_create';
    const TYPE_SNAPSHOT_RESTORE = 'proxmox::messages.logs.snapshot_restore';
    const TYPE_BACKUP_CREATE = 'proxmox::messages.logs.backup_create';
    const TYPE_BACKUP_DESTROY = 'proxmox::messages.logs.backup_destroy';
    const TYPE_BACKUP_RESTORE = 'proxmox::messages.logs.backup_restore';


    public static function getLogs($service_id, $limit = 10)
    {
        return self::where('service_id', $service_id)->orderBy('created_at', 'desc')->limit($limit)->get();
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public static function addLog(Service $service, string $type, string $user, int $vmid)
    {
        if (!in_array($type, [
            self::TYPE_START,
            self::TYPE_STOP,
            self::TYPE_RESTART,
            self::TYPE_DESTROY,
            self::TYPE_REINSTALL,
            self::TYPE_REINSTALL_DONE,
        ])) {
            return;
        }
        $log = new self();
        $log->service_id = $service->id;
        $log->type = $type;
        $log->user = $user;
        $log->vmid = $vmid;
        $log->created_at = date('Y-m-d H:i:s');
        $log->save();
    }

}
