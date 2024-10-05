<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Addons\Import;

use App\Addons\Import\Controllers\ImportController;
use App\Extensions\BaseAddonServiceProvider;
use App\Extensions\BaseModuleServiceProvider;
use App\Http\Controllers\Admin\Settings\SettingsExtensionController;
use App\Models\Core\Permission;

class ImportServiceProvider extends BaseAddonServiceProvider
{
    protected string $uuid = 'import';
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $service = $this->app->make('settings');
        $this->loadViews();
        $this->loadTranslationsFrom(addon_path('import', 'lang'), 'import');
        \Route::middleware(['web', 'admin'])
            ->prefix(admin_prefix())
            ->name('admin.')
            ->group(function () {
                require addon_path('import', 'routes/admin.php');
            });
        $service->addCardItem('extensions', 'import', 'import::import.title', 'import::import.description', 'bi bi-file-earmark-bar-graph-fill', action([ImportController::class, 'index']), Permission::MANAGE_EXTENSIONS);

    }
}
