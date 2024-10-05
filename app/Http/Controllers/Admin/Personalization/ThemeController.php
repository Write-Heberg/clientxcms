<?php

namespace App\Http\Controllers\Admin\Personalization;

use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;

class ThemeController extends \App\Http\Controllers\Controller
{

    public function showTheme()
    {
        staff_aborts_permission(\App\Models\Core\Permission::MANAGE_PERSONALIZATION);
        $errors = session('errors', new ViewErrorBag());
        $theme = app('theme')->getTheme();
        return view('admin.personalization.settings.theme', ['configHTML' => $theme->configView(['errors' => $errors]), 'themes' => app('theme')->getThemes(), 'currentTheme' => app('theme')->getTheme()]);
    }

    public function switchTheme(\Request $request, string $theme)
    {
        staff_aborts_permission(\App\Models\Core\Permission::MANAGE_PERSONALIZATION);
        app('theme')->setTheme($theme, true);
        \App\Theme\ThemeManager::clearCache();
        return redirect()->back();
    }

    public function configTheme(Request $request)
    {
        staff_aborts_permission(\App\Models\Core\Permission::MANAGE_PERSONALIZATION);
        $theme = app('theme')->getTheme();
        try {
            $theme->storeConfig($request->all());
            return redirect()->back()->with('success', __('personalization.config.success'));
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator);
        }
    }
}
