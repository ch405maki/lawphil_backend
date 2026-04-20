<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Api\v1\ProfilePictureController;
use App\Http\Controllers\Web\JurisprudenceController;
use App\Http\Controllers\Web\AdministrativeOrderController;
use App\Http\Controllers\Web\ExecutiveOrderController;
use App\Http\Controllers\Web\MemorandumCircularController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('dashboard', function () {   
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // jurisprudence
    Route::prefix('jurisprudence')->group(function () {
    Route::get('/', [JurisprudenceController::class, 'index'])->name('jurisprudence.index');
    Route::get('/create', [JurisprudenceController::class, 'create'])->name('jurisprudence.create');
    });

    // administrative order
    Route::prefix('administrative')->group(function () {
    Route::get('/', [AdministrativeOrderController::class, 'index'])->name('administrative.index');
    Route::get('/create', [AdministrativeOrderController::class, 'create'])->name('administrative.create');
    });

    // executive orders
    Route::prefix('executive-orders')->group(function () {
        Route::get('/', [ExecutiveOrderController::class, 'index'])->name('executive-orders.index');
        Route::get('/create', [ExecutiveOrderController::class, 'create'])->name('executive-orders.create');
    });

    // memorandum circulars
    Route::prefix('memorandum-circulars')->group(function () {
    Route::get('/', [MemorandumCircularController::class, 'index'])->name('memorandum-circulars.index');
    Route::get('/create', [MemorandumCircularController::class, 'create'])->name('memorandum-circulars.create');
});

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    
    Route::get('/profile-pictures', [ProfilePictureController::class, 'index'])->name('profile-pictures.index');
    Route::post('/profile-pictures', [ProfilePictureController::class, 'store'])->name('profile-pictures.store');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';