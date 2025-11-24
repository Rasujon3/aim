<?php

use App\Modules\Products\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/products')->middleware(['auth:api'])->group(function () {
    Route::get('/list', [ProductController::class, 'index'])
        ->name('products.list')
        ->middleware('permission:view');

    Route::post('/create', [ProductController::class, 'store'])
        ->name('products.store')
        ->middleware('permission:create');

    Route::get('/view/{product}', [ProductController::class, 'show'])
        ->name('products.view')
        ->middleware('permission:view');

    Route::post('/update/{product}', [ProductController::class, 'update'])
        ->name('products.update')
        ->middleware('permission:edit');

    Route::delete('/delete/{product}', [ProductController::class, 'destroy'])
        ->name('products.delete')
        ->middleware('permission:delete');
});
