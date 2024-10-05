<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
use App\Modules\Proxmox\Controllers\PowerController;

Route::name('proxmox')
    ->name('proxmox.')
    ->prefix('proxmox')
    ->middleware('throttle:proxmox-power-actions')
    ->group(function() {
        \Route::post('/power/{service}/{power}', [PowerController::class, 'power'])
            ->name('power')
            ->where('power', 'start|stop|reboot');
        Route::post('/reinstall/{service}', [PowerController::class, 'reinstall'])
            ->name('reinstall');
    });
