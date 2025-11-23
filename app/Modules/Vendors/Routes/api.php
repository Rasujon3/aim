<?php

use App\Modules\Vendors\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/vendors')->middleware(['auth:api'])->group(function () {
    Route::get('/list', [VendorController::class, 'index'])
        ->name('vendors.list')
        ->middleware('permission:view');

    Route::post('/create', [VendorController::class, 'store'])
        ->name('vendors.store')
        ->middleware('permission:create');

    Route::get('/view/{category}', [VendorController::class, 'show'])
        ->name('vendors.view')
        ->middleware('permission:view');

    Route::post('/update/{category}', [VendorController::class, 'update'])
        ->name('vendors.update')
        ->middleware('permission:edit');

    Route::delete('/delete/{category}', [VendorController::class, 'destroy'])
        ->name('vendors.delete')
        ->middleware('permission:delete');
});
