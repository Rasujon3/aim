<?php

use App\Modules\Invoices\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/invoices')->middleware(['auth:api'])->group(function () {
    Route::get('/list', [InvoiceController::class, 'index'])
        ->name('invoices.list')
        ->middleware('permission:view');

    Route::post('/create', [InvoiceController::class, 'create'])
        ->name('invoices.store')
        ->middleware('permission:create');

    Route::get('/view/{invoice}', [InvoiceController::class, 'show'])
        ->name('invoices.view')
        ->middleware('permission:view');

    Route::post('/update/{invoice}', [InvoiceController::class, 'update'])
        ->name('invoices.update')
        ->middleware('permission:edit');

    Route::delete('/delete/{invoice}', [InvoiceController::class, 'destroy'])
        ->name('invoices.delete')
        ->middleware('permission:delete');
});
