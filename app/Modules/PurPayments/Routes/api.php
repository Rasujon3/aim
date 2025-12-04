<?php

use App\Modules\PurPayments\Controllers\PurPaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/purPayments')->middleware(['auth:api'])->group(function () {
    Route::get('/list', [PurPaymentController::class, 'index'])
        ->name('purPayments.list')
        ->middleware('super_admin_or_admin');

    Route::post('/create', [PurPaymentController::class, 'create'])
        ->name('purPayments.store')
        ->middleware('super_admin_or_admin');

    Route::get('/view/{payment}', [PurPaymentController::class, 'show'])
        ->name('purPayments.view')
        ->middleware('super_admin_or_admin');

    Route::post('/update/{payment}', [PurPaymentController::class, 'update'])
        ->name('purPayments.update')
        ->middleware('super_admin_or_admin');

    Route::delete('/delete/{payment}', [PurPaymentController::class, 'destroy'])
        ->name('purPayments.delete')
        ->middleware('super_admin_or_admin');
});
