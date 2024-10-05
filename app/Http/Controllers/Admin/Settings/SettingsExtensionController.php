<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin\Settings;

use App\DTO\Core\Extensions\ExtensionDTO;
use App\Models\ActionLog;
use App\Models\Core\Permission;
use Illuminate\Filesystem\Filesystem;

class SettingsExtensionController
{

    public function showExtensions()
    {
        $extensions = app('extension')->getAllExtensions(false);
        return view('admin.settings.extensions.index', ['extensions' => $extensions]);
    }

    public function enable(string $type, string $extension)
    {
        staff_aborts_permission(Permission::MANAGE_EXTENSIONS);

        if (!in_array($type, ['modules', 'addons', 'themes'])) {
            abort(404);
        }
        if (app('extension')->extensionIsEnabled($extension)) {
            return redirect()->back()->with('error', __('extensions.flash.already_enabled'));
        }
        $composerFile = base_path($type . '/' . $extension . '/composer.json');
        if (!file_exists($composerFile)) {
            return redirect()->back()->with('error', __('extensions.flash.composer_not_found'));
        }
        if (!app('extension')->canBeActivated($extension)){
            return redirect()->back()->with('error', __('extensions.flash.cannot_enable'));
        }
        $composerJson = json_decode((new Filesystem())->get($composerFile), true);
        $prerequisites = app('extension')->checkPrerequisites($composerJson);
        if (!empty($prerequisites)) {
            return redirect()->back()->with('error', implode(', ', $prerequisites));
        }
        app(substr($type, 0,strlen($type) - 1))->onEnable($extension);
        try {
            app('extension')->enable($type, $extension);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
        \Artisan::call('cache:clear');
        \Artisan::call('view:clear');
        \Artisan::call('config:clear');
        \Artisan::call('migrate', ['--force' => true, '--path' => $type . '/' . $extension . '/database/migrations']);
        \Artisan::call('db:seed', ['--force' => true]);
        ActionLog::log(ActionLog::EXTENSION_ENABLED, ExtensionDTO::class, $extension, auth('admin')->id(), null, ['type' => $type]);
        return redirect()->back();
    }

    public function disable(string $type, string $extension)
    {
        staff_aborts_permission(Permission::MANAGE_EXTENSIONS);
        if (!in_array($type, ['modules', 'addons', 'themes'])) {
            abort(404);
        }
        app(substr($type, 0,strlen($type) - 1))->onDisable($extension);
        app('extension')->disable($type, $extension);
        ActionLog::log(ActionLog::EXTENSION_DISABLED, ExtensionDTO::class, $extension, auth('admin')->id(), null, ['type' => $type]);
        return redirect()->back();
    }

    public function clear()
    {
        staff_aborts_permission(Permission::MANAGE_EXTENSIONS);
        \Artisan::call('cache:clear');
        return redirect()->back()->with('success', __('extensions.flash.cache_cleared'));
    }
}
