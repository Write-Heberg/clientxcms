<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionLogEntries extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'action_log_id',
        'attribute',
        'old_value',
        'new_value',
    ];

    public function actionLog()
    {
        return $this->belongsTo(ActionLog::class);
    }

}
