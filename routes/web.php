<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Api\v1\ProfilePictureController;
use App\Http\Controllers\JurisprudenceController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('dashboard', function () {   
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // 1. Page Views (Inertia)
    Route::get('/jurisprudence', function () {
        return Inertia::render('Jurisprudence/Index'); 
    })->name('jurisprudence.index');

    // 2. Data APIs (JSON)
    Route::prefix('api')->group(function () {
        Route::get('/jurisprudence', [JurisprudenceController::class, 'index'])->name('api.jurisprudence.index');
        // FIXED: Added the update route for your axios.put calls
        Route::put('/jurisprudence/{id}', [JurisprudenceController::class, 'update'])->name('api.jurisprudence.update');
    });

    // 3. Admin/Action Routes
    Route::prefix('admin')->group(function () {
        Route::post('/jurisprudence/import', [JurisprudenceController::class, 'import'])->name('jurisprudence.import');
        
        // FIXED: Added the truncate route
        Route::delete('/jurisprudence/truncate', [JurisprudenceController::class, 'truncate'])->name('jurisprudence.truncate');
        
        Route::delete('/jurisprudence/{id}', [JurisprudenceController::class, 'destroy'])->name('jurisprudence.destroy');
    });

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/profile-pictures', [ProfilePictureController::class, 'index'])->name('profile-pictures.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';