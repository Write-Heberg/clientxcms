<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Providers;

use App\Http\Controllers\Admin\Core\ActionsLogController;
use App\Http\Controllers\Admin\DatabaseController;
use App\Http\Controllers\Admin\HistoryController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\Settings\SettingsCoreController;
use App\Core\Menu\AdminMenuItem;
use App\Http\Controllers\Admin\Settings\SettingsExtensionController;
use App\Models\Admin\Setting;
use App\Models\Core\Permission;
use App\Services\Core\InvoiceService;
use App\Services\SettingsService;
use App\Services\Store\TaxesService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Storage;
use function Symfony\Component\String\s;

class SettingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('settings', SettingsService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

        try {
            /** @var SettingsService $service */

            $service = $this->app->make('settings');
            $service->set($this->loadSettings());
            // Ajout de valeur par default
            $service->set('app_name', setting('app_name', config('app.name')));
            $service->set('app_url', request()->getSchemeAndHttpHost());
            $service->set('app_timezone', setting('app_timezone', 'Europe/Paris'));
            $service->set('app_address', \setting('app_address', config('app.name'). ', You can set your address in the settings'));
            $this->initImage($service, 'app_logo', 'app_logo', "resources/global/clientxcms_blue.png");
            $this->initImage($service, 'app_logo_text', 'app_logo_text', "resources/global/clientxcms_text.png");
            $this->initImage($service, 'app_favicon', 'app_favicon', "resources/global/favicon.png");
            $service->set('app_debug', config('app.debug', 'false'));
            $service->set('app_env', config('app.env', 'production'));
            $service->set('checkout_customermustbeconfirmed', \setting('checkout_customermustbeconfirmed', false));
            $service->set('app.license.refresh_token', setting('app.license.refresh_token'));
            $service->set('store_mode_tax', setting('store_mode_tax', TaxesService::MODE_TAX_INCLUDED));
            $service->set('store_vat_enabled', setting('store_vat_enabled'));
            $service->set('billing_invoice_prefix', setting('billing_invoice_prefix', "CTX"));
            $service->set('core.services.days_before_creation_renewal_invoice', setting('core_services_days_before_creation_renewal_invoice', 7));
            $service->set('core.services.days_before_expiration', setting('core_services_days_before_expiration', 7));
            $service->set('core_services_notify_expiration_days', \setting('core_services_notify_expiration_days', '7,5,3,1'));
            $service->set('app_default_locale', setting('app_default_locale', 'fr_FR'));
            $service->set('mail_greeting', setting('mail_greeting', __('global.mail.greeting')));
            $service->set('mail_salutation', setting('mail_salutation', __('global.mail.salutation')));
            $service->set('mail_fromaddress', env('MAIL_FROM_ADDRESS'));
            $service->set('mail_fromname', env('MAIL_FROM_NAME'));
            $service->set('mail_smtp_host', env('MAIL_HOST'));
            $service->set('mail_smtp_port', env('MAIL_PORT', '587'));
            $service->set('mail_smtp_username', env('MAIL_USERNAME'));
            $service->set('mail_smtp_password', env('MAIL_PASSWORD'));
            $service->set('mail_smtp_encryption', env('MAIL_ENCRYPTION'));
            $service->set('mail_smtp_enable', env('MAIL_MAILER') == 'smtp');
            $service->set('mail_domain', env('APP_URL', request()->getSchemeAndHttpHost()));
            $service->set('theme_footer_description', setting('theme_footer_description', \setting('app_name') .  ' You can modify this text in the settings. Powered By CLIENTXCMS'));
            $service->set('theme_home_enabled', setting('theme_home_enabled', 'true'));
            $service->set('theme_switch_mode', setting('theme_switch_mode', 'both'));
            $service->set('seo_site_title', setting('seo_site_title', ' - ' .\setting('app_name')));
            $service->set('theme_home_title_meta', setting('theme_home_title_meta', setting('app_name')));
            $this->initImage($service, 'theme_home_image', 'theme_home_image', 'resources/global/home.png');
            $service->set('helpdesk_ticket_auto_close_days', setting('helpdesk_ticket_auto_close_days', 7));
            $service->set('helpdesk_attachments_max_size', setting('helpdesk_attachments_max_size', 5));
            $service->set('helpdesk_allow_attachments', setting('helpdesk_allow_attachments'));
            $service->set('helpdesk_attachments_allowed_types', setting('helpdesk_attachments_allowed_types', 'jpg,jpeg,png,pdf,doc,docx,xls,xlsx'));
            $service->set('helpdesk_reopen_days', setting('helpdesk_reopen_days', 7));
            $service->set('billing_mode', setting('billing_mode', InvoiceService::INVOICE));
            $service->set('allow_registration', setting('allow_registration', true));
            $service->set('auto_confirm_registration', setting('auto_confirm_registration', false));
            $service->set('allow_reset_password', setting('allow_reset_password', true));
            $service->set('force_password_reset', setting('force_password_reset', false));
            $service->set('force_login_client', setting('force_login_client', false));
            $service->set('banned_emails', setting('banned_emails', ''));
            $service->set('captcha_driver', setting('captcha_driver', 'none'));
            $service->set('maintenance_enabled', setting('maintenance_enabled', false));
            $service->set('maintenance_message', setting('maintenance_message', __('maintenance.in_maintenance_message')));
            $service->set('maintenance_url', setting('maintenance_url', '/maintenancebypass/' . md5(\Str::random(12))));
            $service->set('maintenance_button_link', setting('maintenance_button_link', null));
            $service->set('maintenance_button_text', setting('maintenance_button_text', null));
            $service->set('maintenance_button_icon', setting('maintenance_button_icon', 'bi bi-box-arrow-up-right'));
            $this->initSettings($service);
        } catch (\Throwable $e) {
        }
        $this->loadCards($service);
    }

    protected function initImage(SettingsService $service, string $key, string $setting, string $default): void
    {
        $image = setting($setting);
        if ($image) {
            $service->set($key, Storage::url($image));
        } else {
            $service->set($key, \Vite::asset($default));
        }
    }
    protected function initSettings(SettingsService $service)
    {
        $this->app->setLocale(str_replace('_', '-', $service->get('app.default_locale', 'fr')));
        Carbon::setLocale(config('app.locale'));
        setlocale(LC_ALL, 'fr_FR');
        date_default_timezone_set($service->get('app.timezone'));
        config(['auth.password_timeout' => $service->get('password_timeout', 10800)]);
    }
    protected function loadCards(SettingsService $service)
    {
        $service->addCard('core', 'admin.settings.core.title', 'admin.settings.core.description', 1);
        $service->addCard('extensions', 'extensions.settings.title', 'extensions.settings.description', 3);
        $service->addCard('security', 'admin.security.title', 'admin.security.description', 2);
        $service->addCardItem('core', 'app', 'admin.settings.core.app.title', 'admin.settings.core.app.description', 'bi bi-app-indicator', [SettingsCoreController::class, 'showAppSettings'], Permission::MANAGE_SETTINGS);
        $service->set('app.cron.last_run', setting('app.cron.last_run', null));
        $service->addCardItem('core', 'mail', 'admin.settings.core.mail.title', 'admin.settings.core.mail.description', 'bi bi-envelope-at', [SettingsCoreController::class, 'showEmailSettings'], Permission::MANAGE_SETTINGS);
        $service->addCardItem('core', 'services', 'admin.settings.core.services.title', 'admin.settings.core.services.description', 'bi bi-box2', [SettingsCoreController::class, 'showServicesSettings'], Permission::MANAGE_SETTINGS);
        $service->addCardItem('core', 'maintenance', 'maintenance.settings.title', 'maintenance.settings.description', 'bi bi-toggle-on', [SettingsCoreController::class, 'showMaintenanceSettings'], Permission::MANAGE_SETTINGS);
        $service->addCardItem('security', 'admin', 'admin.admins.title', 'admin.admins.description', 'bi bi-person-badge', route('admin.staffs.index'), 'admin.manage_staffs');
        $service->addCardItem('core', 'license', 'admin.license.title', 'admin.license.description', 'bi bi-key', action([LicenseController::class, 'index']), 'admin.manage_license');
        $service->addCardItem('security', 'database', 'admin.database.title', 'admin.database.description', 'bi bi-database', action([DatabaseController::class, 'index']), 'admin.manage_database');
        $service->addCardItem('security', 'security', 'admin.settings.core.security.title', 'admin.settings.core.security.description', 'bi bi-shield-lock', [SettingsCoreController::class, 'showSecuritySettings'], Permission::MANAGE_SETTINGS);
        $service->addCardItem('extensions', 'extensions', 'extensions.title', 'extensions.description', 'bi bi-palette2', [SettingsExtensionController::class, 'showExtensions'], Permission::MANAGE_EXTENSIONS);
        $service->addCardItem('security', 'history', 'admin.history.title', 'admin.history.description', 'bi bi-archive', action([HistoryController::class, 'index']), 'admin.show_logs');
        $service->addCardItem('security', 'logs', 'actionslog.settings.title', 'actionslog.settings.description', 'bi bi-clock', action([ActionsLogController::class, 'index']), 'admin.show_logs');
        $this->app['extension']->addAdminMenuItem((new AdminMenuItem('settings', 'admin.settings.index', 'bi bi-gear', 'admin.settings.title',10, Permission::ALLOWED)));
    }

    protected function loadSettings(): array
    {
        if ($this->app->runningInConsole()) {
            return Setting::all()->pluck('value', 'name')->all();
        }

        return Cache::remember('settings', now()->addDay(), function () {
            return Setting::all()->pluck('value', 'name')->all();
        });
    }
}
