<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Api\v1\ProfilePictureController;
use App\Http\Controllers\Api\v1\JurisprudenceController;
use App\Http\Controllers\Api\v1\JurisprudenceImportController;
use App\Http\Controllers\Api\v1\MemorandumOrderController;
use App\Http\Controllers\Api\v1\GeneralOrderController;
// BAGO: Import Controllers
use App\Http\Controllers\Api\v1\MemorandumOrderImportController;
use App\Http\Controllers\Api\v1\GeneralOrderImportController;

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

// Memorandum Orders Management
Route::prefix('memorandum-orders')->group(function () {
    Route::get('/', [MemorandumOrderController::class, 'index'])->name('memorandum-orders.index');
    Route::post('/', [MemorandumOrderController::class, 'store'])->name('memorandum-orders.store');
    Route::post('/bulk-delete', [MemorandumOrderController::class, 'bulkDelete'])->name('memorandum-orders.bulk-delete');
    Route::post('/{id}', [MemorandumOrderController::class, 'update'])->name('memorandum-orders.update');
    Route::delete('/{id}', [MemorandumOrderController::class, 'destroy'])->name('memorandum-orders.destroy');
});

// General Orders Management
Route::prefix('general-orders')->group(function () {
    Route::get('/', [GeneralOrderController::class, 'index'])->name('general-orders.index');
    Route::post('/', [GeneralOrderController::class, 'store'])->name('general-orders.store');
    Route::post('/bulk-delete', [GeneralOrderController::class, 'bulkDelete'])->name('general-orders.bulk-delete');
    Route::post('/{id}', [GeneralOrderController::class, 'update'])->name('general-orders.update');
    Route::delete('/{id}', [GeneralOrderController::class, 'destroy'])->name('general-orders.destroy');
});

Route::prefix('v1')->group(function () {
    // Jurisprudence Import
    Route::post('/jurisprudence/import', [JurisprudenceImportController::class, 'import']);
    Route::get('/jurisprudence/import/template', [JurisprudenceImportController::class, 'downloadTemplate']);
    
    // Memorandum Orders Import (BAGO)
    Route::post('/memorandum-orders/import', [MemorandumOrderImportController::class, 'import']);
    
    // General Orders Import (BAGO)
    Route::post('/general-orders/import', [GeneralOrderImportController::class, 'import']);
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