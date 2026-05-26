<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Api\v1\ProfilePictureController;
use App\Http\Controllers\Api\v1\JurisprudenceController;
use App\Http\Controllers\Api\v1\JurisprudenceImportController;
use App\Http\Controllers\Api\v1\PresidentialController;
use App\Http\Controllers\Api\v1\PresidentialImportController;
use App\Http\Controllers\Api\v1\ProclamationController;
use App\Http\Controllers\Api\v1\ProclamationImportController;
use App\Http\Controllers\Api\v1\RepublicController;
use App\Http\Controllers\Api\v1\RepublicImportController;
use App\Http\Controllers\Api\v1\ExecordController;
use App\Http\Controllers\Api\v1\ExecordImportController;
use App\Http\Controllers\Api\v1\AOController;
use App\Http\Controllers\Api\v1\AOImportController;
use App\Http\Controllers\Api\v1\MOController;
use App\Http\Controllers\Api\v1\MOImportController;
use App\Http\Controllers\Api\v1\MCController;
use App\Http\Controllers\Api\v1\MCImportController;
use App\Http\Controllers\Api\v1\GenorController;
use App\Http\Controllers\Api\v1\GenorImportController;
use App\Models\Presidential;

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

Route::prefix('v1')->group(function () {
    Route::post('/jurisprudence/import', [JurisprudenceImportController::class, 'import']);
    Route::get('/jurisprudence/import/template', [JurisprudenceImportController::class, 'downloadTemplate']);
});

// Presidential Management
Route::prefix('v1/presidential')->group(function () {
    Route::post('/import', [PresidentialImportController::class, 'import']);
    Route::get('/import/template', [PresidentialImportController::class, 'downloadTemplate']);
    Route::post('/bulk-delete', [PresidentialController::class, 'bulkDelete'])->name('presidential.bulk-delete');

    Route::get('/', [PresidentialController::class, 'index'])->name('presidential.index');
    Route::post('/', [PresidentialController::class, 'store'])->name('presidential.store');
    Route::post('/{id}', [PresidentialController::class, 'update'])->name('presidential.update');
    Route::delete('/{id}', [PresidentialController::class, 'destroy'])->name('presidential.destroy');
});

// Proclamations Management
Route::prefix('v1/proclamations')->group(function () {
    // Main CRUD

    // Import Features
    Route::post('/import', [ProclamationImportController::class, 'import']);
    Route::get('/import/template', [ProclamationImportController::class, 'downloadTemplate']);
    Route::post('/bulk-delete', [ProclamationController::class, 'bulkDelete'])->name('proclamations.bulk-delete');

    Route::get('/', [ProclamationController::class, 'index'])->name('proclamations.index');
    Route::post('/', [ProclamationController::class, 'store'])->name('proclamations.store');
    Route::post('/{id}', [ProclamationController::class, 'update'])->name('proclamations.update');
    Route::delete('/{id}', [ProclamationController::class, 'destroy'])->name('proclamations.destroy');
});

// Republic Acts Management
Route::prefix('v1/republic')->group(function () {
    // Main CRUD

    // Import Features
    Route::post('/import', [RepublicImportController::class, 'import']);
    Route::get('/import/template', [RepublicImportController::class, 'downloadTemplate']);
    Route::post('/bulk-delete', [RepublicController::class, 'bulkDelete'])->name('republic.bulk-delete');

    Route::get('/', [RepublicController::class, 'index'])->name('republic.index');
    Route::post('/', [RepublicController::class, 'store'])->name('republic.store');
    Route::post('/{id}', [RepublicController::class, 'update'])->name('republic.update');
    Route::delete('/{id}', [RepublicController::class, 'destroy'])->name('republic.destroy');
});

// Executive Orders 
Route::prefix('v1/execord')->group(function () {
    // Main CRUD

    // Import Features
    Route::post('/import', [ExecordImportController::class, 'import']);
    Route::get('/import/template', [ExecordImportController::class, 'downloadTemplate']);
    Route::post('/bulk-delete', [ExecordController::class, 'bulkDelete'])->name('execord.bulk-delete');

    Route::get('/', [ExecordController::class, 'index'])->name('execord.index');
    Route::post('/', [ExecordController::class, 'store'])->name('execord.store');
    Route::post('/{id}', [ExecordController::class, 'update'])->name('execord.update');
    Route::delete('/{id}', [ExecordController::class, 'destroy'])->name('execord.destroy');
});

// Administrative Orders
Route::prefix('v1/ao')->group(function () {
    // Main CRUD

    // Import Features
    Route::post('/import', [AOImportController::class, 'import']);
    Route::get('/import/template', [AOImportController::class, 'downloadTemplate']);
    Route::post('/bulk-delete', [AOController::class, 'bulkDelete'])->name('ao.bulk-delete');

    Route::get('/', [AOController::class, 'index'])->name('ao.index');
    Route::post('/', [AOController::class, 'store'])->name('ao.store');
    Route::post('/{id}', [AOController::class, 'update'])->name('ao.update');
    Route::delete('/{id}', [AOController::class, 'destroy'])->name('ao.destroy');
});

// Memorandum Orders
Route::prefix('v1/mo')->group(function () {
    // Main CRUD

    // Import Features
    Route::post('/import', [MOImportController::class, 'import']);
    Route::get('/import/template', [MOImportController::class, 'downloadTemplate']);
    Route::post('/bulk-delete', [MOController::class, 'bulkDelete'])->name('mo.bulk-delete');

    Route::get('/', [MOController::class, 'index'])->name('mo.index');
    Route::post('/', [MOController::class, 'store'])->name('mo.store');
    Route::post('/{id}', [MOController::class, 'update'])->name('mo.update');
    Route::delete('/{id}', [MOController::class, 'destroy'])->name('mo.destroy');
});

// Memorandum Circulars
Route::prefix('v1/mc')->group(function () {
    // Main CRUD

    // Import Features
    Route::post('/import', [MCImportController::class, 'import']);
    Route::get('/import/template', [MCImportController::class, 'downloadTemplate']);
    Route::post('/bulk-delete', [MCController::class, 'bulkDelete'])->name('mc.bulk-delete');

    Route::get('/', [MCController::class, 'index'])->name('mc.index');
    Route::post('/', [MCController::class, 'store'])->name('mc.store');
    Route::post('/{id}', [MCController::class, 'update'])->name('mc.update');
    Route::delete('/{id}', [MCController::class, 'destroy'])->name('mc.destroy');
});

// General Orders
Route::prefix('v1/genor')->group(function () {
    // Main CRUD

    // Import Features
    Route::post('/import', [GenorImportController::class, 'import']);
    Route::get('/import/template', [GenorImportController::class, 'downloadTemplate']);
    Route::post('/bulk-delete', [GenorController::class, 'bulkDelete'])->name('genor.bulk-delete');

    Route::get('/', [GenorController::class, 'index'])->name('genor.index');
    Route::post('/', [GenorController::class, 'store'])->name('genor.store');
    Route::post('/{id}', [GenorController::class, 'update'])->name('genor.update');
    Route::delete('/{id}', [GenorController::class, 'destroy'])->name('genor.destroy');
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