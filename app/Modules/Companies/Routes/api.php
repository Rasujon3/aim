<?php

use App\Modules\Companies\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/companies')->middleware(['auth:api'])->group(function () {
    Route::get('/list', [CompanyController::class, 'index'])
        ->name('companies.list')
        ->middleware('permission:view');

    Route::post('/create', [CompanyController::class, 'create'])
        ->name('companies.store')
        ->middleware('permission:create');

    Route::get('/view/{company}', [CompanyController::class, 'show'])
        ->name('companies.view')
        ->middleware('permission:view');

    Route::post('/update/{company}', [CompanyController::class, 'update'])
        ->name('companies.update')
        ->middleware('permission:edit');

    Route::delete('/delete/{company}', [CompanyController::class, 'destroy'])
        ->name('companies.delete')
        ->middleware('permission:delete');
});
