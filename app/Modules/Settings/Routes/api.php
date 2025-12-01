<?php

use App\Modules\Settings\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/settings')->middleware(['auth:api'])->group(function () {
    Route::get('/view', [SettingController::class, 'index'])
        ->name('settings.list')
        ->middleware('permission:view');

    Route::post('/update', [SettingController::class, 'update'])
        ->name('settings.update')
        ->middleware('permission:edit');
});
