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

class TaskResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'command',
        'output',
        'runtime',
        'created_at',
        'updated_at'
    ];

    public function excerptsOutput()
    {
        if (empty($this->output)) {
            return 'empty output';
        }
        return nl2br(substr($this->output, 0, $this->subExcerpts()));
    }

    public function subExcerpts()
    {
        $content = nl2br($this->output);
        try {
            if (empty($content)) {
                return 100;
            }
            $position = strpos($content, "\n", 100);
        } catch (\ValueError $e) {
            $position = false;
        }
        if ($position !== false) {
            return $position;
        }
        return 100;
    }

}
