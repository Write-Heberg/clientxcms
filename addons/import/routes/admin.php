<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
Route::get('import', [\App\Addons\Import\Controllers\ImportController::class, 'index'])->name('import.index');
Route::get('import/v1', [\App\Addons\Import\Controllers\ImportController::class, 'importv1'])->name('import.v1');
Route::get('import/whmcs', [\App\Addons\Import\Controllers\ImportController::class, 'importWHMCS'])->name('import.whmcs');
Route::post('import/v1', [\App\Addons\Import\Controllers\ImportController::class, 'importDataFromv1']);
Route::post('import/whmcs', [\App\Addons\Import\Controllers\ImportController::class, 'importDataFromWHMCS']);
Route::post('report', [\App\Addons\Import\Controllers\ImportController::class, 'downloadReport'])->name('report');
