<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Listeners\Core;

use App\Models\Admin\Setting;
use App\Models\Core\TaskResult;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;

class LastCronRunSaved
{
    public function handle(ScheduledTaskStarting $task): void
    {
        Setting::updateSettings(['app_cron_last_run' => now()], null, false);
    }
}
