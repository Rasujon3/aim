<?php

use App\Modules\Quotations\Controllers\QuotationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/quotations')->middleware(['auth:api'])->group(function () {
    Route::get('/list', [QuotationController::class, 'index'])
        ->name('quotations.list')
        ->middleware('permission:view');

    Route::post('/create', [QuotationController::class, 'create'])
        ->name('quotations.store')
        ->middleware('permission:create');

    Route::get('/view/{quotation}', [QuotationController::class, 'show'])
        ->name('quotations.view')
        ->middleware('permission:view');

    Route::post('/update/{quotation}', [QuotationController::class, 'update'])
        ->name('quotations.update')
        ->middleware('permission:edit');

    Route::delete('/delete/{quotation}', [QuotationController::class, 'destroy'])
        ->name('quotations.delete')
        ->middleware('permission:delete');
});
