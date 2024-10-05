<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Admin\AbstractCrudController;
use App\Models\ActionLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;

class ActionsLogController extends AbstractCrudController
{
    protected string $model = ActionLog::class;
    protected string $routePath = 'admin.logs';
    protected string $viewPath = 'admin.core.actionslog';
    protected string $translatePrefix = "actionslog.settings";
    protected int $perPage = 50;
    protected string $filterField = 'action';
    protected ?string $managedPermission = "admin.show_logs";

    public function show(ActionLog $log)
    {
        $this->checkPermission("show");
        return $this->showView([
            'item' => $log,
        ]);
    }

    protected function getSearchFields(): array
    {
        return [
            'id' => 'ID',
            'model' => 'Model name',
            'model_id' => 'Model ID',
            'old_value' => 'Old value',
            'new_value' => 'New value',
        ];
    }

    protected function getIndexFilters(): array
    {
        return collect(ActionLog::ALL_ACTIONS)->mapWithKeys(function ($action) {
            return [$action => ucfirst(str_replace('_', ' ', $action))];
        })->toArray();
    }

    protected function queryIndex(): LengthAwarePaginator
    {
        return QueryBuilder::for($this->model)
            ->allowedFilters(array_merge(array_keys($this->getSearchFields()),[$this->filterField]))
            ->allowedSorts($this->sorts)
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage)
            ->appends(request()->query());
    }
}
