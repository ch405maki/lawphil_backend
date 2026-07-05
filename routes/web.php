<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Api\v1\ProfilePictureController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\JurisprudenceController;
use App\Http\Controllers\Web\PresidentialController;
use App\Http\Controllers\Web\ProclamationController;
use App\Http\Controllers\Web\RepublicController;
use App\Http\Controllers\Web\ExecordController;
use App\Http\Controllers\Web\AOController;
use App\Http\Controllers\Web\MOController;
use App\Http\Controllers\Web\MCController;
use App\Http\Controllers\Web\GenorController;


Route::get('/', function () {
    return Inertia::render('auth/Login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('/logs', function () {   
        return Inertia::render('Log/Index');
    })->name('logs')->middleware('permission:logs,view');

    // jurisprudence
    Route::prefix('jurisprudence')->group(function () {
    Route::get('/', [JurisprudenceController::class, 'index'])->name('jurisprudence.index')->middleware('permission:jurisprudence,view');
    Route::get('/create', [JurisprudenceController::class, 'create'])->name('jurisprudence.create')->middleware('permission:jurisprudence,create');
    });

    //Presidential
    Route::prefix('presidential')->group(function () {
    Route::get('/', [PresidentialController::class, 'index'])->name('presidential.index')->middleware('permission:presidential,view');
    Route::get('/create', [PresidentialController::class, 'create'])->name('presidential.create')->middleware('permission:presidential,create');
    });

    //Proclamation
    Route::prefix('proclamation')->group(function () {
    Route::get('/', [ProclamationController::class, 'index'])->name('proclamation.index')->middleware('permission:proclamation,view');
    Route::get('/create', [ProclamationController::class, 'create'])->name('proclamation.create')->middleware('permission:proclamation,create');
    });

    //RepubActs
    Route::prefix('republic')->group(function () {
    Route::get('/', [RepublicController::class, 'index'])->name('republic.index')->middleware('permission:republic,view');
    Route::get('/create', [RepublicController::class, 'create'])->name('republic.create')->middleware('permission:republic,create');
    });

    //execord
    Route::prefix('execord')->group(function () {
    Route::get('/', [ExecordController::class, 'index'])->name('execord.index')->middleware('permission:execord,view');
    Route::get('/create', [ExecordController::class, 'create'])->name('execord.create')->middleware('permission:execord,create');
    });

    //ao
    Route::prefix('ao')->group(function () {
    Route::get('/', [AOController::class, 'index'])->name('ao.index')->middleware('permission:ao,view');
    Route::get('/create', [AOController::class, 'create'])->name('ao.create')->middleware('permission:ao,create');
    });

    //mo
    Route::prefix('mo')->group(function () {
    Route::get('/', [MOController::class, 'index'])->name('mo.index')->middleware('permission:mo,view');
    Route::get('/create', [MOController::class, 'create'])->name('mo.create')->middleware('permission:mo,create');
    });

    //mc
    Route::prefix('mc')->group(function () {
    Route::get('/', [MCController::class, 'index'])->name('mc.index')->middleware('permission:mc,view');
    Route::get('/create', [MCController::class, 'create'])->name('mc.create')->middleware('permission:mc,create');
    });

    //genor
    Route::prefix('genor')->group(function () {
    Route::get('/', [GenorController::class, 'index'])->name('genor.index')->middleware('permission:genor,view');
    Route::get('/create', [GenorController::class, 'create'])->name('genor.create')->middleware('permission:genor,create');
    });

    Route::get('/users', [UserController::class, 'index'])->name('users.index')->middleware('permission:users,view');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:users,delete');
    Route::post('/users', [UserController::class, 'store'])->name('users.store')->middleware('permission:users,create');
    
    Route::get('/profile-pictures', [ProfilePictureController::class, 'index'])->name('profile-pictures.index');
    Route::post('/profile-pictures', [ProfilePictureController::class, 'store'])->name('profile-pictures.store');

    Route::get('/permissions', function () {
        return Inertia::render('Configurations/Permissions/Index');
    })->name('permissions.index')->middleware('permission:users,view');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';