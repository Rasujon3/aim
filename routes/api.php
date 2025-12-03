<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;

Route::prefix('/v1')->middleware('api')->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/verify-otp', [LoginController::class, 'verifyOtp']);
    Route::middleware('auth:api')->group(function () {
        Route::post('/register', [RegisterController::class, 'register'])->middleware('super_admin_or_admin')->name('user.register');
        Route::get('/user-info', [RegisterController::class, 'userInfo'])->name('user.info');
        Route::get('/user-list', [RegisterController::class, 'userList'])->name('user.list');
        Route::post('/user-profile-update', [RegisterController::class, 'userProfileUpdate'])->name('user.profile.update');
        Route::post('/user-profile-delete', [RegisterController::class, 'userProfileDelete'])->name('user.profile.delete');
        Route::post('/logout', [LoginController::class, 'logout']);
        Route::post('/refresh', [LoginController::class, 'refresh']);
        Route::get('/user', function () {
            return response()->json(auth('api')->user());
        });
    });

    Route::post('/change-password', [RegisterController::class, 'changePassword'])->name('user.change-password');

    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp']); // OTP or Email
    Route::post('/verify-reset-otp', [ForgotPasswordController::class, 'verifyResetOtp']); // OTP verification
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPasswords']); // Reset via OTP
    Route::post('/password/reset', [ForgotPasswordController::class, 'resetPasswordWithToken']); // Reset via email token

    Route::post('/auth/forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
    Route::post('/auth/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
    Route::post('/auth/reset-password', [ForgotPasswordController::class, 'resetPassword']);

});

Route::get('/migrate', function(){
    Artisan::call('migrate', [
        '--force' => true,
    ]);
    return response()->json(['message' => 'Migrations run successfully.']);
});

Route::get('/db-seed', function(){
    Artisan::call('db:seed', [
        '--force' => true,
    ]);
    return response()->json(['message' => 'Database seeded successfully.']);
});

Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('optimize:clear');

    return 'All caches (cache, config, route, optimize) have been cleared!';
});
