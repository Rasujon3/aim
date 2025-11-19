<?php

use App\Modules\Buildings\Controllers\BuildingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/buildings')->middleware(['auth:sanctum', 'roles:owner,receptionist'])->group(function () {
    Route::get('/list', [BuildingController::class, 'index'])->name('buildings.list'); // List data
    Route::post('/create', [BuildingController::class, 'store'])->name('buildings.store'); // Create data
    Route::get('/view/{building}', [BuildingController::class, 'show'])->name('buildings.view'); // View data
    Route::post('/update/{building}', [BuildingController::class, 'update'])->name('buildings.update'); // Update data
    Route::delete('/delete/{building}', [BuildingController::class, 'destroy'])->name('buildings.delete'); // Delete data
});
