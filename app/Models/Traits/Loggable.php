<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Traits;

use App\Models\ActionLog;
use Illuminate\Database\Eloquent\Model;

trait Loggable
{

    public function getLogData(string $event): array
    {
        return [];
    }

    public function shouldLogAttribute(string $attribute): bool
    {
        if ($attribute === $this->getCreatedAtColumn()
            || $attribute === $this->getUpdatedAtColumn()) {
            return false;
        }

        if (count($this->getVisible()) > 0) {
            return in_array($attribute, $this->getVisible(), true);
        }

        return ! in_array($attribute, $this->getHidden(), true);
    }

    public function createLogEntries(ActionLog $log, string $event): void
    {
        if ($event !== 'updated') {
            return;
        }

        foreach ($this->getDirty() as $attribute => $value) {
            $original = $this->getOriginal($attribute);

            if ($this->shouldLogAttribute($attribute) && $this->isValidLogType($original) && $this->isValidLogType($value)) {
                $log->entries()->create([
                    'attribute' => $attribute,
                    'old_value' => $original,
                    'new_value' => $value,
                ]);
            }
        }
    }

    public function isValidLogType($value): bool
    {
        return $value === null || is_bool($value)
            || is_string($value) || is_numeric($value);
    }

    public function getLogsAction(string $action): mixed
    {
        $model = $this;
        return ActionLog::where('model_id', $model->id)
            ->orderBy('created_at', 'desc')
            ->where('model', get_class($model))
            ->where('action', $action);
    }
}
