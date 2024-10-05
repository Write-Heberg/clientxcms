<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin;

use App\DTO\Core\Extensions\ExtensionDTO;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\Console\Output\BufferedOutput;

class DatabaseController extends Controller
{
    public function index()
    {
        staff_aborts_permission('admin.database');
        $extensions = collect(app('extension')->getAllExtensions())->mapWithKeys(function (ExtensionDTO $extension) {
            return [$extension->uuid => $extension->name()];
        })->toArray();
        $extensions['core'] = 'Core';
        $card = app('settings')->getCards()->firstWhere('uuid', 'security');
        if (!$card) {
            abort(404);
        }
        $item = $card->items->firstWhere('uuid', 'database');
        \View::share('current_card', $card);
        \View::share('current_item', $item);
        return view('admin.settings.core.database', compact('extensions'));
    }

    public function migrate(Request $request)
    {
        staff_aborts_permission('admin.database');
        $extension = $request->input('extension');
        $output = new BufferedOutput();
        if ($extension == 'core') {
            \Artisan::call('migrate', ['--force' => true], $output);
            return back()->with('success', 'Database migrated successfully')->with('output', $output->fetch());
        }
        \Artisan::call('clientxcms:db-extension', ['--extension' => $extension], $output);
        return back()->with('success', 'Database migrated successfully')->with('output', $output->fetch());
    }
}
