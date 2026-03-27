<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Api\v1\ProfilePictureController;
use App\Http\Controllers\Api\v1\JurisprudenceController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Jurisprudence Management
Route::prefix('jurisprudence')->group(function () {
    Route::get('/', [JurisprudenceController::class, 'index'])->name('jurisprudence.index');
    Route::post('/import', [JurisprudenceController::class, 'import'])->name('jurisprudence.import');
    Route::post('/{id}', [JurisprudenceController::class, 'update'])->name('jurisprudence.update');
    Route::delete('/{id}', [JurisprudenceController::class, 'destroy'])->name('jurisprudence.destroy');
});

// User Management
Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::post('/users', [UserController::class, 'store']);
Route::post('/upload-users', [UserController::class, 'uploadUsers']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::patch('/users/{user}/status', [UserController::class, 'updateStatus']);

Route::prefix('profile-pictures')->group(function () {
    Route::post('/', [ProfilePictureController::class, 'store'])->name('profile-pictures.store');
});