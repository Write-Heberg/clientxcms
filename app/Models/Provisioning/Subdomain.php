<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Provisioning;

use App\Models\Traits\Loggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subdomain extends Model
{
    use HasFactory, Loggable;

    protected $fillable = ['domain'];

    public function getDomainAttribute($value)
    {
        if ($value == null){
            return null;
        }
        if ($value[0] != '.')
        {
            return '.' . $value;
        }
        return $value;
    }
}
