<?php

use App\Modules\Notes\Controllers\NoteController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/notes')->middleware(['auth:api'])->group(function () {
    Route::get('/list', [NoteController::class, 'index'])
        ->name('notes.list')
        ->middleware('permission:view');

    Route::post('/create', [NoteController::class, 'create'])
        ->name('notes.store')
        ->middleware('permission:create');

    Route::get('/view/{note}', [NoteController::class, 'show'])
        ->name('notes.view')
        ->middleware('permission:view');

    Route::post('/update/{note}', [NoteController::class, 'update'])
        ->name('notes.update')
        ->middleware('permission:edit');

    Route::delete('/delete/{note}', [NoteController::class, 'destroy'])
        ->name('notes.delete')
        ->middleware('permission:delete');
});
