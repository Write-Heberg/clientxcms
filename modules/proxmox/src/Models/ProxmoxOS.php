<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox\Models;

use App\Models\Traits\Loggable;
use App\Traits\OseSVGTrait;
use Illuminate\Database\Eloquent\Model;

class ProxmoxOS extends Model
{
    use OseSVGTrait, Loggable;
    protected $table = 'proxmox_oses';
    protected $fillable = ['name', 'osnames', 'server_id'];

    protected $casts = [
        'osnames' => 'array',
    ];
}
