<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    const MANAGE_EXTENSIONS = 'admin.manage_extensions';
    const MANAGE_PERSONALIZATION = 'admin.manage_personalization';
    const MANAGE_SETTINGS = 'admin.manage_settings';
    const ALLOWED = 'admin.allowed';

    protected $fillable = [
        'name',
        'label',
        'group'
    ];
}
