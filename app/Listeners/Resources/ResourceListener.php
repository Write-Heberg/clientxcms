<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Listeners\Resources;

use App\Events\Resources\AbstractResourceEvent;
use App\Models\ActionLog;

class ResourceListener
{

    public function handle(AbstractResourceEvent $event): void
    {
        $eventName = $event->event;
        $model = $event->model;
        $action = 'resource_'.$eventName;
        if (empty($model->getChanges())){
            return;
        }
        $staffId = auth('admin')->id();
        $customerId = auth('web')->id();
        if (!method_exists($model, 'getLogData')){
            return;
        }
        $log = ActionLog::log($action, get_class($model), $model->getKey(), $staffId, $customerId, $model->getLogData($eventName));

        if ($log !== null) {
            $model->createLogEntries($log, $eventName);
        }
    }
}
