<?php

use App\Modules\Categories\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/categories')->middleware(['auth:api'])->group(function () {
    Route::get('/list', [CategoryController::class, 'index'])->name('categories.list')->middleware('permission:view');
    Route::post('/create', [CategoryController::class, 'store'])->name('categories.store')->middleware('permission:create');
    Route::get('/view/{category}', [CategoryController::class, 'show'])->name('categories.view')->middleware('permission:view');
    Route::post('/update/{category}', [CategoryController::class, 'update'])->name('categories.update')->middleware('permission:edit');
    Route::delete('/delete/{category}', [CategoryController::class, 'destroy'])->name('categories.delete')->middleware('permission:delete');
});
