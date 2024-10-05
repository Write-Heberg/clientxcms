<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox;

use App\Models\Core\Permission;
use App\Modules\Proxmox\Commands\ProxmoxDeleteVPS;
use App\Modules\Proxmox\Commands\ProxmoxDiskVPS;
use App\Modules\Proxmox\Commands\ProxmoxInstallationVPS;
use App\Modules\Proxmox\Controllers\IPAMController;
use App\Modules\Proxmox\Controllers\OsesController;
use App\Modules\Proxmox\Controllers\TemplatesController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use RateLimiter;

class ProxmoxServiceProvider extends \App\Extensions\BaseModuleServiceProvider
{
    protected string $name = "Proxmox";
    protected string $version = "1.0.0";
    protected string $uuid = "proxmox";

    public function boot(): void
    {

        RateLimiter::for('proxmox-power-actions', function ($job) {
            return Limit::perMinute(5)
            ->by(optional($job->user())->id ?: $job->ip());
        });
        $this->loadViews();
        $this->loadTranslations();
        $this->loadMigrations();
        $this->registerProductTypes();
        $this->registerSchedule();
        if ($this->app->runningInConsole()) {
            $this->commands([
                ProxmoxDeleteVPS::class,
                ProxmoxInstallationVPS::class,
                ProxmoxDiskVPS::class,
            ]);
        }
        $service = app('settings');
        \Route::middleware('web')->group(module_path('proxmox', 'routes/web.php'));

        \Route::middleware(['web', 'admin'])->prefix(admin_prefix('proxmox'))->name('admin.proxmox.')->group(function () {
                require module_path('proxmox', 'routes/admin.php');
            });
        $service->addCard('proxmox', 'proxmox::messages.settings.title', 'proxmox::messages.settings.description', 3);
        $service->addCardItem('proxmox', 'ipam', 'proxmox::messages.ipam.title', 'proxmox::messages.ipam.description', 'bi bi-reception-4', action([IPAMController::class, 'index']), Permission::MANAGE_EXTENSIONS);
        $service->addCardItem('proxmox', 'templates', 'proxmox::messages.templates.title', 'proxmox::messages.templates.description', 'bi bi-copy', action([TemplatesController::class, 'index']), Permission::MANAGE_EXTENSIONS);
        $service->addCardItem('proxmox', 'oses', 'proxmox::messages.oses.title', 'proxmox::messages.oses.description', 'bi bi-usb-plug-fill', action([OsesController::class, 'index']), Permission::MANAGE_EXTENSIONS);
        $service->addCardItem('proxmox', 'logs', 'proxmox::messages.logs.title', 'proxmox::messages.logs.description', 'bi bi-archive', action([IPAMController::class, 'logs']), Permission::MANAGE_EXTENSIONS);

    }

    protected function productsTypes(): array
    {
        return [
            ProxmoxProductType::class,
        ];
    }

    public function schedule(Schedule $schedule): void
    {
        if ($this->app->runningInConsole()) {
            $schedule->command('proxmox:delete-vps')
                ->everyMinute()
                ->name('proxmox:delete-vps')
                ->sendOutputTo(storage_path('logs/proxmox-delete-vps.log'))
                ->evenInMaintenanceMode();
            $schedule->command('proxmox:installation-vps')
                ->everyMinute()
                ->name('proxmox:installation-vps')
                ->sendOutputTo(storage_path('logs/proxmox-installation-vps.log'))
                ->evenInMaintenanceMode();
        }
    }

}
