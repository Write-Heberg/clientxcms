<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Provisioning;

use App\Casts\EncryptCast;
use App\Models\Traits\HasMetadata;
use App\Models\Traits\Loggable;
use App\Models\Traits\ModelStatutTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    use HasFactory, ModelStatutTrait,HasMetadata, Loggable;

    protected $fillable = [
        'name',
        'port',
        'username',
        'password',
        'type',
        'address',
        'hostname',
        'maxaccounts',
        'status',
    ];
    protected $attributes = [
        'status' => 'active',
        'port' => 443,
    ];
    protected $casts = [
        'username' => EncryptCast::class,
        'password' => EncryptCast::class,
    ];

}
