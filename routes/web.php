<?php

use App\Http\Controllers\ProfileController;
use \App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // for profile routing
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // for teams routing
    Route::get('/teams', [\App\Http\Controllers\TeamController::class, 'index'])->name('teams.index');

    // for leave request
    Route::get('/Leave', [\App\Http\Controllers\LeaveController::class, 'index'])->name('leave.index');
    Route::post('/Leave/store', [\App\Http\Controllers\LeaveController::class, 'store'])->name('leave.store');

    // for admin routing
    Route::group(['middleware' => ['role:admin']], function () {

        // for user Routing
        Route::get('/users', [UserController::class, 'index'])->name('user.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users/store', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // for teams routing
        Route::get('/teams/create', [\App\Http\Controllers\TeamController::class, 'create'])->name('teams.create');
        Route::post('/teams/store', [\App\Http\Controllers\TeamController::class, 'store'])->name('teams.store');
        Route::delete('/teams/{team}', [\App\Http\Controllers\TeamController::class, 'destroy'])->name('teams.destroy');
        Route::patch('/teams/{team}', [\App\Http\Controllers\TeamController::class, 'update'])->name('teams.update');
        Route::get('/teams/{team}/edit', [\App\Http\Controllers\TeamController::class, 'edit'])->name('teams.edit');
        // for leave Request

    });
    Route::group(['middleware' => ['role:admin|project_manager']], function () {
        Route::post('/Leave/approve', [\App\Http\Controllers\LeaveController::class, 'approve'])->name('leave.approve');
        Route::post('/Leave/refuse', [\App\Http\Controllers\LeaveController::class, 'refuse'])->name('leave.refuse');
    });

});

require __DIR__ . '/auth.php';
