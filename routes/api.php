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
use App\Http\Controllers\Api\v1\ActivityLogController;
use App\Http\Controllers\Api\v1\PermissionController;
use App\Http\Controllers\Api\v1\ArchiveController;
use App\Http\Controllers\Api\v1\ActController;
use App\Http\Controllers\Api\v1\ActImportController;
use App\Http\Controllers\Api\v1\BatasPambansaController;
use App\Http\Controllers\Api\v1\BatasPambansaImportController;
use App\Http\Controllers\Api\v1\CommonWealthController;
use App\Http\Controllers\Api\v1\CommonWealthImportController;

/*
|--------------------------------------------------------------------------
| Public API (no authentication required)
|--------------------------------------------------------------------------
*/

Route::prefix('public')->group(function () {
    Route::get('/archives/{module}', [ArchiveController::class, 'show']);
    Route::get('/jurisprudence', [JurisprudenceController::class, 'index']);
    Route::get('/presidential', [PresidentialController::class, 'index']);
    Route::get('/proclamations', [ProclamationController::class, 'index']);
    Route::get('/republic', [RepublicController::class, 'index']);
    Route::get('/execord', [ExecordController::class, 'index']);
    Route::get('/ao', [AOController::class, 'index']);
    Route::get('/mo', [MOController::class, 'index']);
    Route::get('/mc', [MCController::class, 'index']);
    Route::get('/genor', [GenorController::class, 'index']);
    Route::get('/acts', [ActController::class, 'index']);
    Route::get('/bataspambansa', [BatasPambansaController::class, 'index']);
    Route::get('/commonwealth', [CommonWealthController::class, 'index']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Backward compatibility for cached clients
Route::get('/jurisprudence', [JurisprudenceController::class, 'index'])->name('jurisprudence.index');

// Jurisprudence Management
Route::middleware('auth:sanctum')->prefix('jurisprudence')->group(function () {
    Route::post('/', [JurisprudenceController::class, 'store'])->name('jurisprudence.store');
    Route::post('/{id}', [JurisprudenceController::class, 'update'])->name('jurisprudence.update');
    Route::delete('/{id}', [JurisprudenceController::class, 'destroy'])->name('jurisprudence.destroy');
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('/jurisprudence/import', [JurisprudenceImportController::class, 'import'])->middleware('permission:jurisprudence,create');
    Route::get('/jurisprudence/import/template', [JurisprudenceImportController::class, 'downloadTemplate']);
});

// Presidential Management
Route::prefix('v1/presidential')->middleware('auth:sanctum')->group(function () {
    Route::post('/import', [PresidentialImportController::class, 'import'])->middleware('permission:presidential,create');
    Route::get('/import/template', [PresidentialImportController::class, 'downloadTemplate']);
    Route::get('/', [PresidentialController::class, 'index'])->name('presidential.index')->middleware('permission:presidential,view');
    Route::post('/', [PresidentialController::class, 'store'])->name('presidential.store')->middleware('permission:presidential,create');
    Route::post('/{id}', [PresidentialController::class, 'update'])->name('presidential.update')->middleware('permission:presidential,update');
    Route::delete('/{id}', [PresidentialController::class, 'destroy'])->name('presidential.destroy')->middleware('permission:presidential,delete');
});

// Proclamations Management
Route::prefix('v1/proclamations')->middleware('auth:sanctum')->group(function () {
    Route::post('/import', [ProclamationImportController::class, 'import'])->middleware('permission:proclamation,create');
    Route::get('/import/template', [ProclamationImportController::class, 'downloadTemplate']);
    Route::get('/', [ProclamationController::class, 'index'])->name('proclamations.index')->middleware('permission:proclamation,view');
    Route::post('/', [ProclamationController::class, 'store'])->name('proclamations.store')->middleware('permission:proclamation,create');
    Route::post('/{id}', [ProclamationController::class, 'update'])->name('proclamations.update')->middleware('permission:proclamation,update');
    Route::delete('/{id}', [ProclamationController::class, 'destroy'])->name('proclamations.destroy')->middleware('permission:proclamation,delete');
});

// Republic Acts Management
Route::prefix('v1/republic')->middleware('auth:sanctum')->group(function () {
    Route::post('/import', [RepublicImportController::class, 'import'])->middleware('permission:republic,create');
    Route::get('/import/template', [RepublicImportController::class, 'downloadTemplate']);
    Route::get('/', [RepublicController::class, 'index'])->name('republic.index')->middleware('permission:republic,view');
    Route::post('/', [RepublicController::class, 'store'])->name('republic.store')->middleware('permission:republic,create');
    Route::post('/{id}', [RepublicController::class, 'update'])->name('republic.update')->middleware('permission:republic,update');
    Route::delete('/{id}', [RepublicController::class, 'destroy'])->name('republic.destroy')->middleware('permission:republic,delete');
});

// Executive Orders 
Route::prefix('v1/execord')->middleware('auth:sanctum')->group(function () {
    Route::post('/import', [ExecordImportController::class, 'import'])->middleware('permission:execord,create');
    Route::get('/import/template', [ExecordImportController::class, 'downloadTemplate']);
    Route::get('/', [ExecordController::class, 'index'])->name('execord.index')->middleware('permission:execord,view');
    Route::post('/', [ExecordController::class, 'store'])->name('execord.store')->middleware('permission:execord,create');
    Route::post('/{id}', [ExecordController::class, 'update'])->name('execord.update')->middleware('permission:execord,update');
    Route::delete('/{id}', [ExecordController::class, 'destroy'])->name('execord.destroy')->middleware('permission:execord,delete');
});

// Administrative Orders
Route::prefix('v1/ao')->middleware('auth:sanctum')->group(function () {
    Route::post('/import', [AOImportController::class, 'import'])->middleware('permission:ao,create');
    Route::get('/import/template', [AOImportController::class, 'downloadTemplate']);
    Route::get('/', [AOController::class, 'index'])->name('ao.index')->middleware('permission:ao,view');
    Route::post('/', [AOController::class, 'store'])->name('ao.store')->middleware('permission:ao,create');
    Route::post('/{id}', [AOController::class, 'update'])->name('ao.update')->middleware('permission:ao,update');
    Route::delete('/{id}', [AOController::class, 'destroy'])->name('ao.destroy')->middleware('permission:ao,delete');
});

// Memorandum Orders
Route::prefix('v1/mo')->middleware('auth:sanctum')->group(function () {
    Route::post('/import', [MOImportController::class, 'import'])->middleware('permission:mo,create');
    Route::get('/import/template', [MOImportController::class, 'downloadTemplate']);
    Route::get('/', [MOController::class, 'index'])->name('mo.index')->middleware('permission:mo,view');
    Route::post('/', [MOController::class, 'store'])->name('mo.store')->middleware('permission:mo,create');
    Route::post('/{id}', [MOController::class, 'update'])->name('mo.update')->middleware('permission:mo,update');
    Route::delete('/{id}', [MOController::class, 'destroy'])->name('mo.destroy')->middleware('permission:mo,delete');
});

// Memorandum Circulars
Route::prefix('v1/mc')->middleware('auth:sanctum')->group(function () {
    Route::post('/import', [MCImportController::class, 'import'])->middleware('permission:mc,create');
    Route::get('/import/template', [MCImportController::class, 'downloadTemplate']);
    Route::get('/', [MCController::class, 'index'])->name('mc.index')->middleware('permission:mc,view');
    Route::post('/', [MCController::class, 'store'])->name('mc.store')->middleware('permission:mc,create');
    Route::post('/{id}', [MCController::class, 'update'])->name('mc.update')->middleware('permission:mc,update');
    Route::delete('/{id}', [MCController::class, 'destroy'])->name('mc.destroy')->middleware('permission:mc,delete');
});

// General Orders
Route::prefix('v1/genor')->middleware('auth:sanctum')->group(function () {
    Route::post('/import', [GenorImportController::class, 'import'])->middleware('permission:genor,create');
    Route::get('/import/template', [GenorImportController::class, 'downloadTemplate']);
    Route::get('/', [GenorController::class, 'index'])->name('genor.index')->middleware('permission:genor,view');
    Route::post('/', [GenorController::class, 'store'])->name('genor.store')->middleware('permission:genor,create');
    Route::post('/{id}', [GenorController::class, 'update'])->name('genor.update')->middleware('permission:genor,update');
    Route::delete('/{id}', [GenorController::class, 'destroy'])->name('genor.destroy')->middleware('permission:genor,delete');
});

// Acts
Route::prefix('v1/acts')->middleware('auth:sanctum')->group(function () {
    Route::post('/import', [ActImportController::class, 'import'])->middleware('permission:acts,create');
    Route::get('/import/template', [ActImportController::class, 'downloadTemplate']);
    Route::get('/', [ActController::class, 'index'])->name('acts.index')->middleware('permission:acts,view');
    Route::post('/', [ActController::class, 'store'])->name('acts.store')->middleware('permission:acts,create');
    Route::post('/{id}', [ActController::class, 'update'])->name('acts.update')->middleware('permission:acts,update');
    Route::delete('/{id}', [ActController::class, 'destroy'])->name('acts.destroy')->middleware('permission:acts,delete');
});

// Batas Pambansa
Route::prefix('v1/batas-pambansa')->middleware('auth:sanctum')->group(function () {
    Route::post('/import', [BatasPambansaImportController::class, 'import'])->middleware('permission:batas_pambansa,create');
    Route::get('/import/template', [BatasPambansaImportController::class, 'downloadTemplate']);
    Route::get('/', [BatasPambansaController::class, 'index'])->name('batas_pambansa.index')->middleware('permission:batas_pambansa,view');
    Route::post('/', [BatasPambansaController::class, 'store'])->name('batas_pambansa.store')->middleware('permission:batas_pambansa,create');
    Route::post('/{id}', [BatasPambansaController::class, 'update'])->name('batas_pambansa.update')->middleware('permission:batas_pambansa,update');
    Route::delete('/{id}', [BatasPambansaController::class, 'destroy'])->name('batas_pambansa.destroy')->middleware('permission:batas_pambansa,delete');
});

// Commonwealth
Route::prefix('v1/commonwealth')->middleware('auth:sanctum')->group(function () {
    Route::post('/import', [CommonWealthImportController::class, 'import'])->middleware('permission:commonwealth,create');
    Route::get('/import/template', [CommonWealthImportController::class, 'downloadTemplate']);
    Route::get('/', [CommonWealthController::class, 'index'])->name('commonwealth.index')->middleware('permission:commonwealth,view');
    Route::post('/', [CommonWealthController::class, 'store'])->name('commonwealth.store')->middleware('permission:commonwealth,create');
    Route::post('/{id}', [CommonWealthController::class, 'update'])->name('commonwealth.update')->middleware('permission:commonwealth,update');
    Route::delete('/{id}', [CommonWealthController::class, 'destroy'])->name('commonwealth.destroy')->middleware('permission:commonwealth,delete');
});

// User Management
Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('permission:users,delete');
    Route::post('/users', [UserController::class, 'store'])->middleware('permission:users,create');
    Route::post('/upload-users', [UserController::class, 'uploadUsers'])->middleware('permission:users,create');
    Route::put('/users/{id}', [UserController::class, 'update'])->middleware('permission:users,update');
    Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])->middleware('permission:users,update');
});

// Activity log
Route::get('/activity-logs', [ActivityLogController::class, 'index'])->middleware('auth:sanctum', 'permission:logs,view');

Route::prefix('profile-pictures')->middleware('auth:sanctum')->group(function () {
    Route::post('/', [ProfilePictureController::class, 'store'])->name('profile-pictures.store');
});

// Permission management
Route::middleware('auth:sanctum', 'permission:users,view')->prefix('v1/permissions')->group(function () {
    Route::get('/', [PermissionController::class, 'index']);
    Route::post('/update', [PermissionController::class, 'update']);
    Route::post('/add-role', [PermissionController::class, 'addRole']);
});