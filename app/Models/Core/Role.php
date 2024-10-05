<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Core;

use App\Models\Admin\Admin;
use App\Models\Traits\Loggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    use Loggable;

    protected $fillable = [
        'name',
        'level',
        'is_admin',
        'is_default'
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function staffs()
    {
        return $this->hasMany(Admin::class);
    }

    public function hasPermission($permission)
    {
        if ($this->is_admin){
            return true;
        }
        if ($permission == Permission::ALLOWED)
            return true;
        return $this->permissions->contains('name', $permission);
    }

    public function hasAnyPermission($permissions)
    {
        if ($this->is_admin)
            return true;
        return $this->permissions->whereIn('name', $permissions)->isNotEmpty();
    }

    public function hasAllPermissions($permissions)
    {
        if ($this->is_admin)
            return true;
        return $this->permissions->whereIn('name', $permissions)->count() == count($permissions);
    }

    public function isAdmin()
    {
        return $this->is_admin;
    }

    public function isDefault()
    {
        return $this->is_default;
    }

}
