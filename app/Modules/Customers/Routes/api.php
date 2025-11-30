<?php

use App\Modules\Customers\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/customers')->middleware(['auth:api'])->group(function () {
    Route::get('/list', [CustomerController::class, 'index'])
        ->name('customers.list')
        ->middleware('permission:view');

    Route::post('/create', [CustomerController::class, 'create'])
        ->name('customers.store')
        ->middleware('permission:create');

    Route::get('/view/{customer}', [CustomerController::class, 'show'])
        ->name('customers.view')
        ->middleware('permission:view');

    Route::post('/update/{customer}', [CustomerController::class, 'update'])
        ->name('customers.update')
        ->middleware('permission:edit');

    Route::delete('/delete/{customer}', [CustomerController::class, 'destroy'])
        ->name('customers.delete')
        ->middleware('permission:delete');

    Route::post('/assign-user', [CustomerController::class, 'assignUser'])
        ->name('customers.assignUser')
        ->middleware('super_admin_or_admin');
});
