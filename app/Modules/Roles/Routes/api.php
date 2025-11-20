<?php

use App\Modules\Roles\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/roles')->middleware(['auth:api'])->group(function () {
    Route::get('/list', [RoleController::class, 'index'])->name('roles.list'); // List data
    Route::post('/create', [RoleController::class, 'store'])->name('roles.store'); // Create data
    Route::get('/view/{role}', [RoleController::class, 'show'])->name('roles.view'); // View data
    Route::post('/update/{role}', [RoleController::class, 'update'])->name('roles.update'); // Update data
    Route::delete('/delete/{role}', [RoleController::class, 'destroy'])->name('roles.delete'); // Delete data
});
