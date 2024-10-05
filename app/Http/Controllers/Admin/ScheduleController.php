<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin;

use App\Models\Core\TaskResult;
use Illuminate\Http\Request;

class ScheduleController
{
    public function index(Request $request)
    {
        $filters = TaskResult::groupBy('command')->pluck('command')->toArray();
        if ($request->has('filter')) {
            $filter = $request->get('filter');
            if (!in_array($filter, $filters)){
                return redirect()->route('admin.schedules.index');
            }
            $items = TaskResult::orderBy('created_at', 'desc')->where('command', $request->get('filter'))->paginate(50);
        } else {
            $filter = null;
            $items = TaskResult::orderBy('created_at', 'desc')->paginate(10);
        }
        return view('admin.schedules.index', [
            'items' => $items,
            'filter' => $filter,
            'filters' => $filters,
        ]);
    }
}
