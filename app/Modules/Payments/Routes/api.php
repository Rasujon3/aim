<?php

use App\Modules\Payments\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/payments')->middleware(['auth:api'])->group(function () {
    Route::get('/list', [PaymentController::class, 'index'])
        ->name('payments.list')
        ->middleware('permission:view');

    Route::post('/create', [PaymentController::class, 'create'])
        ->name('payments.store')
        ->middleware('permission:create');

    Route::get('/view/{payment}', [PaymentController::class, 'show'])
        ->name('payments.view')
        ->middleware('permission:view');

    Route::post('/update/{payment}', [PaymentController::class, 'update'])
        ->name('payments.update')
        ->middleware('permission:edit');

    Route::delete('/delete/{payment}', [PaymentController::class, 'destroy'])
        ->name('payments.delete')
        ->middleware('permission:delete');

    Route::post('/assign-user', [PaymentController::class, 'assignUser'])
        ->name('payments.assignUser')
        ->middleware('super_admin_or_admin');
});
