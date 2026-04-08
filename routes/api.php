<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Api\v1\ProfilePictureController;
use App\Http\Controllers\Api\v1\JurisprudenceController;
use App\Http\Controllers\Api\v1\JurisprudenceImportController;
use App\Http\Controllers\Api\v1\ExecutiveOrderImportController;
use App\Http\Controllers\Api\v1\MemorandumCircularController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Jurisprudence Management
Route::prefix('jurisprudence')->group(function () {
    Route::get('/', [JurisprudenceController::class, 'index'])->name('jurisprudence.index');
    Route::post('/', [JurisprudenceController::class, 'store'])->name('jurisprudence.store');
    Route::post('/bulk-delete', [JurisprudenceController::class, 'bulkDelete'])->name('jurisprudence.bulk-delete');
    Route::post('/{id}', [JurisprudenceController::class, 'update'])->name('jurisprudence.update');
    Route::delete('/{id}', [JurisprudenceController::class, 'destroy'])->name('jurisprudence.destroy');
});

// Executive Order Management
Route::prefix('executive-orders')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\v1\ExecutiveOrderController::class, 'index']);
    Route::post('/', [App\Http\Controllers\Api\v1\ExecutiveOrderController::class, 'store']);
    Route::post('/bulk-delete', [App\Http\Controllers\Api\v1\ExecutiveOrderController::class, 'bulkDelete']);
    Route::post('/{id}', [App\Http\Controllers\Api\v1\ExecutiveOrderController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\Api\v1\ExecutiveOrderController::class, 'destroy']);
});

// Memorandum Circular Management
Route::prefix('memorandum-circulars')->group(function () {
    Route::get('/', [MemorandumCircularController::class, 'index']);
    Route::post('/', [MemorandumCircularController::class, 'store']);
    Route::post('/bulk-delete', [MemorandumCircularController::class, 'bulkDelete']);
    Route::post('/{id}', [MemorandumCircularController::class, 'update']);
    Route::delete('/{id}', [MemorandumCircularController::class, 'destroy']);
});

Route::prefix('v1')->group(function () {
    Route::post('/jurisprudence/import', [JurisprudenceImportController::class, 'import']);
    Route::get('/jurisprudence/import/template', [JurisprudenceImportController::class, 'downloadTemplate']);

    Route::post('/executive-orders/import', [ExecutiveOrderImportController::class, 'import']);
    Route::get('/executive-orders/import/template', [ExecutiveOrderImportController::class, 'downloadTemplate']);
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