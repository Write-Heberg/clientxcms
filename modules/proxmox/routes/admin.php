<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
Route::resource('ipam', \App\Modules\Proxmox\Controllers\IPAMController::class);
Route::post('/ipam/mass_action', [\App\Modules\Proxmox\Controllers\IPAMController::class, 'massAction'])->name('ipam.mass_action');
Route::resource('templates', \App\Modules\Proxmox\Controllers\TemplatesController::class);
Route::resource('oses', \App\Modules\Proxmox\Controllers\OsesController::class);
Route::post('ipam/ranges', [\App\Modules\Proxmox\Controllers\IPAMController::class, 'ranges'])->name('ipam.ranges');
Route::get('logs', [\App\Modules\Proxmox\Controllers\IPAMController::class, 'logs'])->name('logs');
