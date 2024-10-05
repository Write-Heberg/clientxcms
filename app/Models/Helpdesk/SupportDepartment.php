<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Helpdesk;

use App\Models\Traits\Loggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportDepartment extends Model
{
    use HasFactory, Loggable;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'staff_subscribers'
    ];

    protected $casts = [
        'staff_subscribers' => 'array'
    ];

    protected $attributes = [
        'icon' => 'bi bi-question-circle',
    ];

    public function tickets()
    {
        return $this->hasMany(SupportTicket::class);
    }
    protected static function newFactory()
    {
        return \Database\Factories\Helpdesk\DepartmentFactory::new();
    }
}
