<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
use App\Http\Controllers\InstallController;

Route::get('/settings', [InstallController::class, 'showSettings'])->name('settings');
Route::post('/settings', [InstallController::class, 'storeSettings']);
Route::get('/register', [InstallController::class, 'showRegister'])->name('register');
Route::post('/register', [InstallController::class, 'storeRegister']);
Route::get('/summary', [InstallController::class, 'showSummary'])->name('summary');
Route::post('/summary', [InstallController::class, 'storeSummary']);
