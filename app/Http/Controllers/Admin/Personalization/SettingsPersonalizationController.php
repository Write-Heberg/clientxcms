<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin\Personalization;

use App\Http\Controllers\Controller;
use App\Models\Admin\Setting;
use App\Models\Core\Permission;
use App\Models\Personalization\MenuLink;
use App\Theme\ThemeManager;
use Illuminate\Http\Request;
use Session;

class SettingsPersonalizationController extends Controller
{
    public function showFrontMenu()
    {
        $menu = MenuLink::where('type', 'front')->first();
        if ($menu == null) {
            $menu = MenuLink::create(MenuLink::newFrontMenu());
        }
        if (Session::has('menu_items')) {
            $menu_items = Session::get('menu_items');
            Session::forget('menu_items');
            $menu->fill([
                'items' => $menu_items,
            ]);
        }
        return view('admin.personalization.settings.front', [
            'menu' => $menu,
            'modes' => [
                'light' => __('personalization.theme.fields.theme_switch_mode.light'),
                'dark' => __('personalization.theme.fields.theme_switch_mode.dark'),
                'both' => __('personalization.theme.fields.theme_switch_mode.both'),
            ]
        ]);
    }

    public function showBottomMenu()
    {
        $menu = MenuLink::where('type', 'bottom')->first();
        if ($menu == null) {
            $menu = MenuLink::create(MenuLink::newBottonMenu());
        }
        if (Session::has('menu_items_bottom')) {
            $menu_items = Session::get('menu_items_bottom');
            Session::forget('menu_items_bottom');
            $menu->fill([
                'items' => $menu_items,
            ]);
        }
        return view('admin.personalization.settings.bottom', [
            'menu' => $menu,
        ]);
    }

    public function storeFrontMenu(Request $request)
    {
        staff_aborts_permission(Permission::MANAGE_PERSONALIZATION);

        Session::flash('menu_items', $request->get('menu_items'));
        $this->validate($request, [
            'menu_items' => 'required|array|max:10',
            'menu_items.*.name' => 'required|string|max:25',
            'menu_items.*.url' => 'required|string|max:255',
            'menu_items.*.icon' => 'required|string|max:100',
            'theme_switch_mode' => 'required|in:light,dark,both',
            'theme_header_logo' => 'in:true,false',
        ]);
        $menu = MenuLink::where('type', 'front')->first();
        $menu->update([
            'items' => $request->get('menu_items'),
        ]);
        Setting::updateSettings([
            'theme_switch_mode' => $request->get('theme_switch_mode'),
            'theme_header_logo' => $request->get('theme_header_logo') ?? 'false',
        ]);
        ThemeManager::clearCache();
        return redirect()->back();
    }

    public function storeBottomMenu(Request $request)
    {
        staff_aborts_permission(Permission::MANAGE_PERSONALIZATION);
        Session::flash('menu_items_bottom', $request->get('menu_items'));
        $this->validate($request, [
            'menu_items' => 'required|array|max:10',
            'menu_items.*.name' => 'required|string|max:25',
            'menu_items.*.url' => 'required|string|max:255',
            'theme_footer_description' => 'required|string',
            'theme_footer_topheberg' => 'nullable|string',
        ]);
        $menu = MenuLink::where('type', 'bottom')->first();
        $menu->update([
            'items' => $request->get('menu_items'),
        ]);
        Setting::updateSettings([
            'theme_footer_description' => $request->get('theme_footer_description'),
            'theme_footer_topheberg' => $request->get('theme_footer_topheberg'),
        ]);
        ThemeManager::clearCache();
        return redirect()->back();
    }

    public function storeSeoSettings(Request $request)
    {
        staff_aborts_permission(Permission::MANAGE_SETTINGS);
        $data = $this->validate($request, [
            'seo_headscripts' => 'nullable|string',
            'seo_description' => 'nullable|string',
            'seo_keywords' => 'nullable|string',
            'seo_footscripts' => 'nullable|string',
            'seo_themecolor' => 'nullable|string',
            'seo_disablereferencement' => 'in:true,false',
            'seo_site_title' => 'required|string',
        ]);
        $data['seo_disablereferencement'] = $data['seo_disablereferencement'] ?? 'false';
        Setting::updateSettings($data);
        \Cache::delete('seo_head');
        \Cache::delete('seo_footer');
        return redirect()->back()->with('success', __('personnlization.seo.success'));
    }


    public function storeTheme(Request $request)
    {
        staff_aborts_permission(Permission::MANAGE_PERSONALIZATION);
        $data = $this->validate($request, [
        ]);
        $data['theme_header_logo'] = $data['theme_header_logo'] ?? 'false';
        Setting::updateSettings($data);
        return redirect()->back()->with('success', __('personalization.settings.theme.success'));
    }


    public function showSeoSettings()
    {
        return view('admin.personalization.settings.seo');
    }

    public function showHomeSettings()
    {
        return view('admin.personalization.settings.home');
    }
    public function storeHomeSettings(Request $request)
    {
        staff_aborts_permission(Permission::MANAGE_PERSONALIZATION);
        $data = $this->validate($request, [
            'theme_home_title' => 'required|string',
            'theme_home_subtitle' => 'required|string',
            'theme_home_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'theme_home_enabled' => 'in:true,false',
            'theme_home_title_meta' => 'required|string',
        ]);
        if ($request->hasFile('theme_home_image')) {
            if (\setting('theme_home_image') && \Storage::exists(\setting('theme_home_image')))
                \Storage::delete(\setting('theme_home_image'));
            $file = "home." . $request->file('theme_home_image')->getClientOriginalExtension();
            $file = $request->file('theme_home_image')->storeAs('public' . DIRECTORY_SEPARATOR . 'uploads', $file);
            $data['theme_home_image'] = $file;
        }

        if ($request->remove_theme_home_image == 'true') {
            if (\setting('theme_home_image') && \Storage::exists(\setting('theme_home_image')))
                \Storage::delete(\setting('theme_home_image'));
            $data['theme_home_image'] = null;
            unset($data['remove_theme_home_image']);
        }
        $data['theme_home_enabled'] = $data['theme_home_enabled'] ?? 'false';
        Setting::updateSettings($data);
        return redirect()->back()->with('success', __('personalization.settings.home.success'));
    }


    public function showPrimaryColors()
    {
        $theme = self::getColorsArray();
        $primary_color = $theme['400'];
        $secondary_color = $theme['600'];
        return view('admin.personalization.settings.primary', [
            'primary_color' => $primary_color,
            'secondary_color' => $secondary_color,
        ]);
    }

    public function storePrimaryColors(Request $request)
    {
        staff_aborts_permission(Permission::MANAGE_PERSONALIZATION);
        $this->validate($request, [
            'theme_primary' => 'required|string',
            'theme_secondary' => 'required|string',
            'theme_header_logo' => 'in:true,false',
            'theme_switch_mode' => 'required|in:light,dark,both',
        ]);
        $file = storage_path('app' . DIRECTORY_SEPARATOR . 'theme.json');
        $theme = [
            '50' => '#f0f5ff',
            '100' => '#e5edff',
            '200' => '#cddbfe',
            '300' => '#b4c6fc',
            '400' => $request->get('theme_secondary'),
            '500' => '#6875f5',
            '600' => $request->get('theme_primary'),
            '700' => $request->get('theme_primary'),
            '800' => '#42389d',
            '900' => '#362f78',
        ];
        file_put_contents($file, json_encode($theme));
        Setting::updateSettings([
            'theme_switch_mode' => $request->get('theme_switch_mode'),
            'theme_header_logo' => $request->get('theme_header_logo') ?? 'false',
        ]);
        return redirect()->back()->with('success', __('personalization.settings.theme.success'));
    }

    public function previewPrimary()
    {
        staff_aborts_permission(Permission::MANAGE_PERSONALIZATION);
        Session::put('in_preview_theme', true);
        return redirect()->to('/');
    }

    public static function getColorsArray()
    {
        $file = storage_path('app' . DIRECTORY_SEPARATOR . 'theme.json');
        if (file_exists($file)) {
            $theme = json_decode(file_get_contents($file), true);
        } else {
            $theme = [
                '50' => '#f0f5ff',
                '100' => '#e5edff',
                '200' => '#cddbfe',
                '300' => '#b4c6fc',
                '400' => '#8da2fb',
                '500' => '#6875f5',
                '600' => '#5850ec',
                '700' => '#5145cd',
                '800' => '#42389d',
                '900' => '#362f78',
            ];
        }
        return $theme;
    }

}
