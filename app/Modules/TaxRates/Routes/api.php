<?php

use App\Modules\TaxRates\Controllers\TaxRateController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/taxRates')->middleware(['auth:api'])->group(function () {
    Route::get('/list', [TaxRateController::class, 'index'])
        ->name('taxRates.list')
        ->middleware('permission:view');

    Route::post('/create', [TaxRateController::class, 'store'])
        ->name('taxRates.store')
        ->middleware('permission:create');

    Route::get('/view/{taxRate}', [TaxRateController::class, 'show'])
        ->name('taxRates.view')
        ->middleware('permission:view');

    Route::post('/update/{taxRate}', [TaxRateController::class, 'update'])
        ->name('taxRates.update')
        ->middleware('permission:edit');

    Route::delete('/delete/{taxRate}', [TaxRateController::class, 'destroy'])
        ->name('taxRates.delete')
        ->middleware('permission:delete');
});
