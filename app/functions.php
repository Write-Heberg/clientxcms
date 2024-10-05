<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
use App\Services\SettingsService;
use App\Services\Store\CurrencyService;

if (! function_exists('setting')) {
    function setting(string $name = null, mixed $default = null): mixed
    {
        /** @var SettingsService $settings */
        $settings = app('settings');

        if ($name === null) {
            return $settings;
        }
        $name = str_replace('.', '_', $name);

        return $settings->get($name, $default);
    }
}
if (!function_exists('ClientX\varExport')) {
    function varExport($expression, $return = false)
    {
        $export = var_export($expression, true);
        $patterns = [
            "/array \(/" => '[',
            "/^([ ]*)\)(,?)$/m" => '$1]$2',
            "/=>[ ]?\n[ ]+\[/" => '=> [',
            "/([ ]*)(\'[^\']+\') => ([\[\'])/" => '$1$2 => $3',
        ];
        $export = preg_replace(array_keys($patterns), array_values($patterns), $export);
        if ((bool)$return) {
            return $export;
        } else {
            echo $export;
        }
    }
}

if (! function_exists('basket')) {
    function basket(bool $force = true)
    {
        return \App\Models\Store\Basket\Basket::getBasket($force);
    }
}

if (! function_exists('is_installed')) {
    function is_installed(): bool
    {
        return file_exists(storage_path('installed'));
    }
}

if (! function_exists('is_demo')) {
    function is_demo(): bool
    {
        return file_exists(storage_path('demo'));
    }
}

if (! function_exists('is_darkmode')) {
    function is_darkmode(): bool
    {
        if (auth('admin')->check()) {
            return auth('admin')->user()->dark_mode ?? setting('theme_switch_mode', 'both') == 'dark';
        }
        if (setting('theme_switch_mode', 'both') == 'light') {
            return false;
        }
        if (auth()->check()) {
            return auth()->user()->dark_mode ?? setting('theme_switch_mode', 'both') == 'dark';
        }
        return Cookie::get('dark_mode', setting('theme_switch_mode', 'both') == 'dark');
    }
}
if (! function_exists('is_lightmode')) {
    function is_lightmode(): bool
    {
        return !is_darkmode();
    }
}

if (! function_exists('format_bytes')) {
    function format_bytes(int $bytes, int $decimals = 2, bool $suffix = true){

        $bytes = (int)$bytes;
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, $decimals) . ($suffix ? ' GB' : '');
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, $decimals) . ($suffix ? ' MB' : '');
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, $decimals) . ($suffix ? ' KB' : '');
        } elseif ($bytes > 1) {
            $bytes = $bytes . ($suffix ? ' bytes' : '');
        } elseif ($bytes == 1) {
            $bytes = $bytes . ($suffix ? ' byte' : '');
        } else {
            $bytes = '0 ' . ($suffix ? ' byte' : '');
        }

        return $bytes;
    }

}
if (! function_exists('currency')) {
    function currency(): string
    {
        return app(CurrencyService::class)->retrieveCurrency();
    }

    function currency_symbol(?string $currency = null): string
    {
        return app(CurrencyService::class)->get($currency ?? currency())['symbol'];
    }

    function currencies()
    {
        return app(CurrencyService::class)->getCurrencies();
    }

    function tax_percent(?string $iso=null): float
    {
        return \App\Services\Store\TaxesService::getVatPercent($iso);
    }

    function formatted_price(float $price, ?string $currency = null): string
    {
        $currency = $currency ?? currency();
        $locale = $currency == 'USD' ? 'en_US' : 'fr_FR';
        return (new NumberFormatter($locale, NumberFormatter::CURRENCY))->formatCurrency($price, $currency);
    }
}
if (! function_exists('module_path')){
    function module_path(string $uuid,string $path = ''): string
    {
        return app('module')->modulePath($uuid, $path);
    }
}

if (! function_exists('addon_path')){
    function addon_path(string $uuid,string $path = ''): string
    {
        return app('addon')->addonPath($uuid, $path);
    }
}
if (! function_exists('theme_manager')){
    function theme_manager(): \App\Theme\ThemeManager
    {
        return app(\App\Theme\ThemeManager::class);
    }
}
if (! function_exists('is_subroute')) {
    function is_subroute(string $route): bool
    {
        return Str::startsWith(request()->url(), $route);
    }
}
if (! function_exists('ctx_version')) {
    function ctx_version(): string
    {
        return \App\Providers\AppServiceProvider::VERSION;
    }
}

if (! function_exists('settings_remplace_dot')) {
    function settings_remplace_dot(array $data): array
    {
        $newData = [];
        foreach ($data as $key => $value) {
            $newData[str_replace('.', '_', $key)] = $value;
        }
        return $newData;
    }
}
if(! function_exists('is_tax_included')) {

    function is_tax_included()
    {
        return setting('store.mode_tax') == \App\Services\Store\TaxesService::MODE_TAX_EXCLUDED;
    }

    function is_tax_excluded()
    {
        return setting('store.mode_tax') == \App\Services\Store\TaxesService::MODE_TAX_INCLUDED;
    }
}

if (! function_exists('formatted_extension_list')){
    function formatted_extension_list($string): string
    {
        $extensions = explode(',', $string);
        $appenddot = function ($ext) {
            return '.' . $ext;
        };
        $appenddot = array_map($appenddot, $extensions);
        return implode(', ', $appenddot);
    }
}

if (! function_exists('staff_has_permission')) {
    function staff_has_permission(string $permission): bool
    {
        if (auth('admin')->check()) {
            return auth('admin')->user()->can($permission);
        }
        return false;
    }

    function staff_aborts_permission(string $permission): void
    {
        abort_if(!staff_has_permission($permission), 403);
    }
}
if (! function_exists('admin_prefix')) {
    function admin_prefix(?string $path = null): string
    {
        if ($path) {
            return env('ADMIN_PREFIX', 'admin') . '/' . $path;
        }
        return env('ADMIN_PREFIX', 'admin');
    }
}
if (! function_exists('theme_config')) {
    function theme_config(string $key, mixed $default = null): mixed
    {
        return app('theme')->getTheme()->config[$key] ?? $default;
    }
}
if (! function_exists('theme_section')) {
    function theme_section(string $uuid): \App\DTO\Core\Extensions\ThemeSectionDTO
    {
        return app('extension')->getThemeSection($uuid);
    }
}

if (! function_exists('render_theme_sections')) {
    function render_theme_sections()
    {
        $url = request()->path();
        if (!str_starts_with($url, '/')) {
            $url = '/' . $url;
        }
        return collect(app('theme')->getSectionsForUrl($url))->reduce(function (string $html, \App\Models\Personalization\Section $section) {
            return $html . $section->toDTO()->render();
        }, '');
    }
}

if (! function_exists('theme_asset')) {
    function theme_asset(string $path): string
    {
        return asset('themes/' . app('theme')->getTheme()->uuid . '/' . $path);
    }
}
