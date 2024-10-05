<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin\Settings;

use App\Helpers\EnvEditor;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\AppSettingsRequest;
use App\Mail\MailTested;
use App\Models\Admin\Setting;
use App\Models\Core\Permission;
use App\Services\Core\LocaleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\RequiredIf;

class SettingsCoreController extends Controller
{
    public function showEmailSettings()
    {
        return view('admin.settings.core.email');
    }

    public function showAppSettings()
    {
        $locales = LocaleService::getLocalesNames();
        $timezones = collect(timezone_identifiers_list())->mapWithKeys(fn ($timezone) => [$timezone => $timezone])->toArray();
        return view('admin.settings.core.app', compact('locales', 'timezones'));
    }

    public function showServicesSettings()
    {
        $variables = ['%serviceid%', '%servicename%', '%customeremail%', '%customername%', '%serviceurl%'];
        return view('admin.settings.core.services', compact('variables'));
    }

    public function showSecuritySettings()
    {
        $drivers = [
            'argon' => 'Argon - For Migrated instances',
            'bcrypt' => 'Bcrypt',
            'argon2id' => 'Argon2id',
        ];
        $captcha = [
            'none' => 'None',
            'recaptcha' => 'Google reCAPTCHA',
            'hcaptcha' => 'hCaptcha',
            'cloudflare' => 'Cloudflare turnstile',
        ];
        return view('admin.settings.core.security', compact('drivers', 'captcha'));
    }

    public function showMaintenanceSettings()
    {
        staff_aborts_permission(Permission::MANAGE_SETTINGS);
        return view('admin.settings.core.maintenance');
    }

    public function storeMaintenanceSettings(Request $request)
    {
        staff_aborts_permission(Permission::MANAGE_SETTINGS);
        $data = $this->validate($request, [
            'maintenance_enabled' => 'nullable',
            'maintenance_url' => 'required|string',
            'maintenance_message' => 'required|string',
            'maintenance_button_text' => 'nullable|string',
            'maintenance_button_icon' => 'nullable|string',
            'maintenance_button_url' => 'nullable|string|url',
        ]);
        $data['maintenance_enabled'] = $data['maintenance_enabled'] ?? false;
        Setting::updateSettings($data);
        return redirect()->back()->with('success', __('maintenance.settings.success'));
    }

    public function storeEmailSettings(Request $request)
    {
        staff_aborts_permission(Permission::MANAGE_SETTINGS);
        $data = $this->validate($request, [
            'mail_from_address' => 'required|string|max:255',
            'mail_from_name' => 'required|string|max:255',
            'mail_salutation' => 'required|string|max:255',
            'mail_greeting' => 'required|string|max:255',
            'mail_domain' => 'required|string',
        ]);
        if ($request->has('mail_smtp_enable') || ($request->filled('mail_smtp_host'))) {
            $data['mail_smtp_enable'] = true;
            if (!$request->has('mail_smtp_enable') && getenv('MAIL_MAILER') == 'smtp')
                $data['mail_smtp_enable'] = false;
            $data = $data + $this->validate($request, [
                'mail_smtp_host' => 'required|string',
                'mail_smtp_port' => 'required|integer|between:1,65535',
                'mail_smtp_username' => 'string',
                'mail_smtp_password' => 'string',
                'mail_smtp_encryption' => 'required|string',
            ]);
            EnvEditor::updateEnv([
                'MAIL_HOST' => $data['mail_smtp_host'],
                'MAIL_PORT' => $data['mail_smtp_port'],
                'MAIL_USERNAME' => $data['mail_smtp_username'],
                'MAIL_PASSWORD' => $data['mail_smtp_password'],
                'MAIL_ENCRYPTION' => $data['mail_smtp_encryption'],
            ]);
        } else {
            $data['mail_smtp_enable'] = false;
        }
        $mailer = $data['mail_smtp_enable'] == 1 ? 'smtp' : 'sendmail';
        if (array_key_exists('mail_disable_mail', $request->all())) {
            $mailer = 'log';
        }
        EnvEditor::updateEnv([
            'MAIL_MAILER' => $mailer,
            'MAIL_FROM_ADDRESS' => $data['mail_from_address'],
            'MAIL_FROM_NAME' => $data['mail_from_name'],
            'APP_URL' => $data['mail_domain'],
        ]);
        Setting::updateSettings($request->only('mail_greeting', 'mail_salutation'));
        return redirect()->back()->with('success', __('admin.settings.core.mail.success'));
    }

    public function storeServicesSettings(Request $request)
    {
        staff_aborts_permission(Permission::MANAGE_SETTINGS);
        $data = $this->validate($request, [
            'core_services_days_before_creation_renewal_invoice' => 'required|integer|min:1',
            'core_services_days_before_expiration' => 'required|integer|min:1',
            'core_services_webhook_url' => 'nullable|url',
            'core_services_notify_expiration_days' => 'nullable|string',
        ]);
        Setting::updateSettings($data);
        return redirect()->back()->with('success', __('admin.settings.core.services.success'));
    }

    public function testmail(Request $request)
    {
        staff_aborts_permission(Permission::MANAGE_SETTINGS);
        try {
            $request->user('admin')->notify(new MailTested($request->user('admin')));
        } catch (\Exception $exception) {
            return response($exception->getMessage(), 500);
        }

        return response('', 204);
    }


    public function storeAppSettings(AppSettingsRequest $request)
    {
        staff_aborts_permission(Permission::MANAGE_SETTINGS);
        $data = $request->validated();
        if ($request->hasFile('app_logo')) {
            if (\setting('app_logo') && \Storage::exists(\setting('app_logo')))
                \Storage::delete(\setting('app_logo'));
            $file = $request->file('app_logo')->storeAs('public', 'app_logo' . rand(1000, 9999) .'.png');;
            $data['app_logo'] = $file;
        }
        if ($request->hasFile('app_favicon')) {
            if (\setting('app_favicon') && \Storage::exists(\setting('app_favicon')))
                \Storage::delete(\setting('app_favicon'));
            $file = $request->file('app_favicon')->storeAs('public', 'app_favicon' . rand(1000, 9999) .'.png');
            $data['app_favicon'] = $file;
        }
        if ($request->hasFile('app_logo_text')) {
            if (\setting('app_logo_text') && \Storage::exists(\setting('app_logo_text')))
                \Storage::delete(\setting('app_logo_text'));
            $file = $request->file('app_logo_text')->storeAs('public', 'app_logo_text' . rand(1000, 9999) .'.png');
            $data['app_logo_text'] = $file;
        }

        if ($request->remove_app_logo == 'true') {
            if (\setting('app_logo') && \Storage::exists(\setting('app_logo')))
                \Storage::delete(\setting('app_logo'));
            $data['app_logo'] = null;
            unset($data['remove_app_logo']);
        }
        if ($request->remove_app_favicon == 'true') {
            if (\setting('app_favicon') && \Storage::exists(\setting('app_favicon')))
                \Storage::delete(\setting('app_favicon'));
            $data['app_favicon'] = null;
            unset($data['remove_app_favicon']);
        }
        if ($request->remove_app_logo_text == 'true') {
            if (\setting('app_logo_text') && \Storage::exists(\setting('app_logo_text')))
                \Storage::delete(\setting('app_logo_text'));
            $data['app_logo_text'] = null;
            unset($data['remove_app_logo_text']);
        }
        EnvEditor::updateEnv([
            'APP_NAME' => $data['app_name'],
            'APP_ENV' => $data['app_env'] == 'production' ? 'production' : 'local',
            'APP_DEBUG' => $data['app_debug'] == 'true' ? 'true' : 'false',
        ]);
        unset($data['app_env']);
        unset($data['app_debug']);
        Setting::updateSettings($data);
        return redirect()->back()->with('success', __('admin.settings.core.app.success'));
    }

    public function storeSecurity(Request $request)
    {
        staff_aborts_permission(Permission::MANAGE_SETTINGS);
        $data = $this->validate($request, [
            'hash_driver' => 'required|string',
            'allow_reset_password' => 'nullable|string|in:true,false',
            'allow_registration' => 'nullable|string|in:true,false',
            'auto_confirm_registration' => 'nullable|string|in:true,false',
            'force_login_client' => 'nullable|string|in:true,false',
            'password_timeout' => 'nullable|integer',
            'banned_emails' => 'nullable|string',
            'captcha_driver' => 'required|string',
            'admin_prefix' => 'required|string',
            'captcha_site_key' => ['string', new RequiredIf($request->captcha_driver != 'none')],
            'captcha_secret_key' => ['string', new RequiredIf($request->captcha_driver != 'none')],
        ]);
        EnvEditor::updateEnv([
            'HASH_DRIVER' => $data['hash_driver'],
            'ADMIN_PREFIX' => $data['admin_prefix'],
        ]);
        $data['allow_reset_password'] = $data['allow_reset_password'] ?? 'false';
        $data['allow_registration'] = $data['allow_registration'] ?? 'false';
        $data['auto_confirm_registration'] = $data['auto_confirm_registration'] ?? 'false';
        $data['force_login_client'] = $data['force_login_client'] ?? 'false';
        Setting::updateSettings($data);
        return redirect()->back()->with('success', __('admin.settings.core.security.success'));
    }
}
