<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Api\v1\ProfilePictureController;

// Jurisprudence Controllers
use App\Http\Controllers\Api\v1\JurisprudenceController;
use App\Http\Controllers\Api\v1\JurisprudenceImportController;

// Administrative Order Controllers
use App\Http\Controllers\Api\v1\AdministrativeOrderController;
use App\Http\Controllers\Api\v1\AdministrativeOrderImportController;
use App\Http\Controllers\Api\v1\MemorandumOrderController;
use App\Http\Controllers\Api\v1\GeneralOrderController;
use App\Http\Controllers\Api\v1\MemorandumOrderImportController;
use App\Http\Controllers\Api\v1\GeneralOrderImportController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/**
 * Jurisprudence Management (Existing)
 */
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

/**
 * API v1 - Administrative Orders
 */
Route::prefix('v1/administrative')->group(function () {
    // 1. Static/Specific routes FIRST
    Route::get('/', [AdministrativeOrderController::class, 'index'])->name('administrative.index');
    Route::post('/', [AdministrativeOrderController::class, 'store'])->name('administrative.store');
    
    // Move these above {id}
    Route::post('/bulk-delete', [AdministrativeOrderController::class, 'bulkDelete'])->name('administrative.bulk-delete');
    Route::post('/import', [AdministrativeOrderImportController::class, 'import']);
    Route::get('/import/template', [AdministrativeOrderImportController::class, 'downloadTemplate']);

    // 2. Dynamic/Wildcard routes LAST
    Route::post('/{id}', [AdministrativeOrderController::class, 'update'])->name('administrative.update');
    Route::delete('/{id}', [AdministrativeOrderController::class, 'destroy'])->name('administrative.destroy');
});

/**
 * API v1 - Imports & Templates
 */
Route::prefix('v1')->group(function () {
    Route::post('/jurisprudence/import', [JurisprudenceImportController::class, 'import']);
    Route::get('/jurisprudence/import/template', [JurisprudenceImportController::class, 'downloadTemplate']);
    
    // Memorandum Orders Import (BAGO)
    Route::post('/memorandum-orders/import', [MemorandumOrderImportController::class, 'import']);
    
    // General Orders Import (BAGO)
    Route::post('/general-orders/import', [GeneralOrderImportController::class, 'import']);
});

/**
 * User & Profile Management
 */
Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::post('/users', [UserController::class, 'store']);
Route::post('/upload-users', [UserController::class, 'uploadUsers']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::patch('/users/{user}/status', [UserController::class, 'updateStatus']);

Route::prefix('profile-pictures')->group(function () {
    Route::post('/', [ProfilePictureController::class, 'store'])->name('profile-pictures.store');
});