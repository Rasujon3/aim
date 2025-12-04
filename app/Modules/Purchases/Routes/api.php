<?php

use App\Modules\Purchases\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/purchases')->middleware(['auth:api'])->group(function () {
    Route::get('/list', [PurchaseController::class, 'index'])
        ->name('purchases.list')
        ->middleware('permission:view');

    Route::post('/create', [PurchaseController::class, 'create'])
        ->name('purchases.store')
        ->middleware('permission:create');

    Route::get('/view/{purchase}', [PurchaseController::class, 'show'])
        ->name('purchases.view')
        ->middleware('permission:view');

    Route::post('/update/{purchase}', [PurchaseController::class, 'update'])
        ->name('purchases.update')
        ->middleware('permission:edit');

    Route::delete('/delete/{purchase}', [PurchaseController::class, 'destroy'])
        ->name('purchases.delete')
        ->middleware('permission:delete');
});
