<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\Auth\TwoFactorAuthenticationController;
use App\Http\Controllers\Admin\Core\ActionsLogController;
use App\Http\Controllers\Admin\Core\AdminController;
use App\Http\Controllers\Admin\Core\EmailController;
use App\Http\Controllers\Admin\Core\InvoiceController;
use App\Http\Controllers\Admin\Core\RoleController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DatabaseController;
use App\Http\Controllers\Admin\Helpdesk\HelpdeskSettingController;
use App\Http\Controllers\Admin\Helpdesk\Support\DepartmentController;
use App\Http\Controllers\Admin\Helpdesk\Support\TicketController;
use App\Http\Controllers\Admin\HistoryController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\MetadataController;
use App\Http\Controllers\Admin\Personalization\SectionController;
use App\Http\Controllers\Admin\Personalization\SettingsPersonalizationController;
use App\Http\Controllers\Admin\Personalization\SocialCrudController;
use App\Http\Controllers\Admin\Personalization\ThemeController;
use App\Http\Controllers\Admin\Provisioning\ServerController;
use App\Http\Controllers\Admin\Provisioning\ServiceController;
use App\Http\Controllers\Admin\Provisioning\SubdomainController;
use App\Http\Controllers\Admin\Settings\SettingsController;
use App\Http\Controllers\Admin\Settings\SettingsCoreController;
use App\Http\Controllers\Admin\Settings\SettingsExtensionController;
use App\Http\Controllers\Admin\Store\CouponController;
use App\Http\Controllers\Admin\Store\GatewayController;
use App\Http\Controllers\Admin\Store\GroupController;
use App\Http\Controllers\Admin\Store\ProductController;
use App\Http\Controllers\Admin\Store\Settings\SettingsStoreController;
use App\Models\Admin\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::redirect('/', admin_prefix('dashboard'));
Route::get('/tokens/application/create', function (Request $request) {
    if (App::environment('production')) {
        abort(404);
    }
    if (Admin::count() == 0){
        Artisan::call('db:seed', ['--class' => 'AdminSeeder']);
    }
    $token = Admin::first()->createToken('test-admin', ['*']);

    return ['token' => $token->plainTextToken];
})->withoutMiddleware('admin')->name('tokens.create');

Route::get('/tokens/customer/create', function (Request $request) {
    if (App::environment('production')) {
        abort(404);
    }
    $token = \App\Models\Account\Customer::first()->createToken('test-customer', ['customer:*']);

    return ['token' => $token->plainTextToken];
})->withoutMiddleware('admin')->name('tokens.create');
Route::put('/metadata', [MetadataController::class, 'update'])->name('metadata.update');
Route::get('/login', [LoginController::class, 'showForm'])->name('login')->withoutMiddleware('admin');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login')->withoutMiddleware('admin');
Route::any('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout')->withoutMiddleware('admin');
Route::get('/2fa', [TwoFactorAuthenticationController::class, 'show'])
    ->withoutMiddleware('auth')
    ->name('auth.2fa');

Route::post('/2fa', [TwoFactorAuthenticationController::class, 'verify'])
    ->withoutMiddleware('auth');
Route::get('/forgot-password', [PasswordResetLinkController::class, 'showForm'])->name('password.request')->withoutMiddleware('admin');
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email')->withoutMiddleware('admin');
Route::get('/reset-password/{token}', [NewPasswordController::class, 'showForm'])->name('password.reset')->withoutMiddleware('admin');
Route::get('/autologin/{id}/{token}', [AuthenticatedSessionController::class, 'autologin'])->whereNumber('id')->name('autologin')->withoutMiddleware('admin');
Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store')->withoutMiddleware('admin');
Route::get('/confirm-password', [AuthenticatedSessionController::class, 'confirmPassword'])->name('password.confirm')->middleware('admin');
Route::post('/confirm-password', [AuthenticatedSessionController::class, 'confirm'])->middleware('admin');
Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
Route::get('/earn', [\App\Http\Controllers\Admin\DashboardController::class, 'earn'])->name('earn')->middleware('password.confirm:admin.password.confirm');
Route::resource('/customers', CustomerController::class)->names('customers')->except('edit');
Route::resource('/subdomains', SubdomainController::class)->names('subdomains')->except('edit');
Route::get('/customers/{customer}/autologin', [CustomerController::class, 'autologin'])->name('customers.autologin');
Route::post('/customers/{customer}/action/{action}', [CustomerController::class, 'action'])->name('customers.action');
Route::get('/auth/customers/logout', [CustomerController::class, 'logout'])->name('customers.logout');
Route::resource('/servers', ServerController::class)->names('servers')->except('edit');
Route::get('/testservers', [ServerController::class, 'test'])->name('servers.test');
Route::resource('/invoices', InvoiceController::class)->names('invoices')->except('edit');
Route::resource('/staffs', AdminController::class)->names('staffs')->except('edit');
Route::get('/profile', [AdminController::class, 'profile'])->name('staffs.profile');
Route::put('/profile', [AdminController::class, 'updateProfile'])->name('staffs.profile');
Route::post('/profile/2fa', [AdminController::class, 'save2fa'])->name('profile.2fa');
Route::get('/profile/download_codes', [AdminController::class, 'downloadCodes'])->name('profile.2fa_codes');
Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
Route::post('/invoices/{invoice}/draft', [InvoiceController::class, 'draft'])->name('invoices.draft');
Route::post('/invoices/{invoice}/validate', [InvoiceController::class, 'validateInvoice'])->name('invoices.validate');
Route::get('/invoices/{invoice}/config', [InvoiceController::class, 'config'])->name('invoices.config');
Route::post('/invoices/{invoice}/deliver/{item}', [InvoiceController::class, 'deliver'])->name('invoices.deliver');
Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
Route::delete('invoices/{invoice_item}/delete', [InvoiceController::class, 'deleteItem'])->name('invoices.deleteitem');
Route::patch('invoices/{invoice_item}/update', [InvoiceController::class, 'updateItem'])->name('invoices.updateitem');
Route::post('/invoices/mass_action', [InvoiceController::class, 'massAction'])->name('invoices.mass_action');
Route::resource('/groups', GroupController::class)->names('groups')->except('edit');
Route::post('/groups/sort', [GroupController::class, 'sort'])->name('groups.sort')->withoutMiddleware('csrf');
Route::resource('/logs', ActionsLogController::class)->names('logs')->except('edit', 'update', 'delete', 'create', 'store');
Route::put('/groups/{group}/clone', [GroupController::class, 'clone'])->name('groups.clone');
Route::resource('/coupons', CouponController::class)->names('coupons')->except('edit');
Route::delete('/coupons/usage/{coupon_usage}', [CouponController::class, 'deleteUsage'])->name('coupons.deleteusage');
Route::resource('/products', ProductController::class)->names('products')->except('edit');
Route::post('/products/{product}/config', [ProductController::class, 'config'])->name('products.config');
Route::put('/products/{product}/clone', [ProductController::class, 'clone'])->name('products.clone');
Route::get('/license', [LicenseController::class, 'index'])->name('license.index')->middleware('password.confirm:admin.password.confirm');
Route::get('/database', [DatabaseController::class, 'index'])->name('database.index')->middleware('password.confirm:admin.password.confirm');
Route::resource('/roles', RoleController::class)->names('roles')->except('edit');
Route::post('/database', [DatabaseController::class, 'index']);
Route::name('history.')->prefix('history')->group(function() {
    Route::get('/', [HistoryController::class, 'index'])->name('index');
    Route::get('/deleteall', [HistoryController::class, 'deleteAll'])->name('deleteall');
    Route::get('/download', [HistoryController::class, 'download'])->name('download');
    Route::get('/delete', [HistoryController::class, 'delete'])->name('delete');
    Route::get('/clear', [HistoryController::class, 'clear'])->name('clear');
});

Route::resource('email_templates', \App\Http\Controllers\Admin\Personalization\EmailTemplateController::class)->names('personalization.email_templates');
Route::name('settings.')->prefix('settings')->middleware('admin')->group(function() {
    Route::get('/', [SettingsController::class, 'index'])->name('index');
    Route::get('/{card}/{uuid}', [SettingsController::class, 'show'])->name('show');
    Route::get('/core/email', [SettingsCoreController::class, 'showEmailSettings'])->name('core.email');
    Route::put('/core/email', [SettingsCoreController::class, 'storeEmailSettings'])->name('core.email');
    Route::put('/core/app', [SettingsCoreController::class, 'storeAppSettings'])->name('core.app');
    Route::put('/personalization/seo', [SettingsPersonalizationController::class, 'storeSeoSettings'])->name('personalization.seo');
    Route::put('/personalization/home', [SettingsPersonalizationController::class, 'storeHomeSettings'])->name('personalization.home');
    Route::put('/core/services', [SettingsCoreController::class, 'storeServicesSettings'])->name('core.services');
    Route::put('/security/security', [SettingsCoreController::class, 'storeSecurity'])->name('core.security');
    Route::put('/core/maintenance', [SettingsCoreController::class, 'storeMaintenanceSettings'])->name('core.maintenance');
    Route::put('/core/database', [DatabaseController::class, 'migrate'])->name('core.database');
    Route::get('/helpdesk/settings', [HelpdeskSettingController::class, 'showSettings'])->name('helpdesk');
    Route::put('/helpdesk/settings', [HelpdeskSettingController::class, 'storeSettings']);
    Route::get('/testmail', [SettingsCoreController::class, 'testmail'])->name('testmail');
    Route::put('/store/gateway/{gateway}', [GatewayController::class, 'saveConfig'])->name('store.gateways.save');
    Route::put('/store/billing', [SettingsStoreController::class, 'saveBilling'])->name('store.billing.save');
    Route::post('/extensions/{type}/{extension}/enable', [SettingsExtensionController::class, 'enable'])->name('extensions.enable');
    Route::post('/extensions/{type}/{extension}/disable', [SettingsExtensionController::class, 'disable'])->name('extensions.disable');
    Route::post('/extensions/clear', [SettingsExtensionController::class, 'clear'])->name('extensions.clear');
});
Route::get('/emails/{email}', [EmailController::class, 'show'])->name('emails.show');
Route::get('/emails', [EmailController::class, 'index'])->name('emails.index');
Route::delete('/emails/{email}', [EmailController::class, 'destroy'])->name('emails.destroy');
Route::resource('/services', ServiceController::class)->names('services')->except('edit');
Route::post('/services/mass_action', [ServiceController::class, 'massAction'])->name('services.mass_action');
Route::post('/services/{service}/renew', [ServiceController::class, 'renew'])->name('services.renew');
Route::post('/services/{service}/delivery', [ServiceController::class, 'delivery'])->name('services.delivery');
Route::post('/services/{service}/reinstall', [ServiceController::class, 'reinstall'])->name('services.reinstall');
Route::post('/services/{service}/update_data', [ServiceController::class, 'updateData'])->name('services.update_data');
Route::get('/services/{service}/{tab}', [ServiceController::class, 'tab'])->name('services.tab');
Route::post('/services/{service}/action/{action}', [ServiceController::class, 'changeStatus'])->name('services.action')->where('action', 'suspend|unsuspend|expire|cancel');
Route::prefix('/schedules')->name('schedules.')->group(function() {
    Route::get('/', [\App\Http\Controllers\Admin\ScheduleController::class, 'index'])->name('index');
});
Route::name('helpdesk.')->prefix('/helpdesk')->group(function(){
    Route::delete('/tickets/{ticket}/close', [TicketController::class, 'close'])->name('tickets.close');
    Route::post('/tickets/{ticket}/reopen', [TicketController::class, 'reopen'])->name('tickets.reopen');
    Route::get('/tickets/{ticket}/download/{attachment}', [TicketController::class, 'download'])->name('tickets.download');
    Route::post('/tickets/{ticket}/reply', [TicketController::class, 'reply'])->name('tickets.reply');
    Route::resource('/tickets', TicketController::class)->names('tickets')->except('edit');
    Route::resource('/departments', DepartmentController::class)->names('departments')->except('edit');
});
Route::name('personalization.')->prefix('/personalization')->group(function(){
    Route::resource('/socials', SocialCrudController::class)->names('socials')->except('index')->except('edit');
    Route::put('/primary', [SettingsPersonalizationController::class, 'storePrimaryColors'])->name('primary');
    Route::get('/previewPrimary', [SettingsPersonalizationController::class, 'previewPrimary'])->name('previewprimary');
    Route::post('/front_menu', [SettingsPersonalizationController::class, 'storeFrontMenu'])->name('front_menu');
    Route::post('/bottom_menu', [SettingsPersonalizationController::class, 'storeBottomMenu'])->name('bottom_menu');
    Route::post('/switch_theme/{theme}', [ThemeController::class, 'switchTheme'])->name('switch_theme');
    Route::post('/config_theme/{theme}', [ThemeController::class, 'configTheme'])->name('config_theme');
    Route::resource('sections', SectionController::class)->names('sections')->except('edit');
    Route::post('/sections/sort', [SectionController::class, 'sort'])->name('sections.sort')->withoutMiddleware('csrf');
    Route::post('/sections/{section}/clone', [SectionController::class, 'clone'])->name('sections.clone');
    Route::post('/sections/{section}/switch', [SectionController::class, 'switch'])->name('sections.switch');
    Route::post('/sections/{section}/restore', [SectionController::class, 'restore'])->name('sections.restore');
    Route::post('/sections/{section}/clone_section', [SectionController::class, 'cloneSection'])->name('sections.clone_section');
});
