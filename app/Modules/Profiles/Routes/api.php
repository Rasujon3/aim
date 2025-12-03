<?php

use App\Modules\Profiles\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/profiles')->middleware(['auth:api'])->group(function () {

    Route::post('/update-data', [ProfileController::class, 'updateData'])
        ->name('profiles.update.data');

    Route::post('/change-password', [ProfileController::class, 'changePassword'])
        ->name('profiles.changePassword');
});
