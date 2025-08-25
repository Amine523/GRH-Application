<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HomeController;
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
    if (auth()->check() && auth()->user()->hasRole('admin')) {
        return app(HomeController::class)->adminIndex();
    } else {
        return app(HomeController::class)->index();
    }
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Team routes
    Route::prefix('teams')->group(function () {
        // Team management
        Route::get('/', [\App\Http\Controllers\TeamController::class, 'index'])->name('teams.index');
        Route::get('/create', [\App\Http\Controllers\TeamController::class, 'create'])->name('teams.create');
        Route::post('/', [\App\Http\Controllers\TeamController::class, 'store'])->name('teams.store');
        Route::get('/{team}', [\App\Http\Controllers\TeamController::class, 'show'])->name('teams.show');
        Route::get('/{team}/edit', [\App\Http\Controllers\TeamController::class, 'edit'])->name('teams.edit');
        Route::patch('/{team}', [\App\Http\Controllers\TeamController::class, 'update'])->name('teams.update');
        Route::delete('/{team}', [\App\Http\Controllers\TeamController::class, 'destroy'])->name('teams.destroy');
        
        // Team member management
        Route::post('/{team}/add-member', [\App\Http\Controllers\TeamController::class, 'addMember'])->name('teams.add-member');
        Route::delete('/{team}/remove-member/{user}', [\App\Http\Controllers\TeamController::class, 'removeMember'])->name('teams.remove-member');
    });
    // Project routes
    Route::prefix('projects')->group(function () {
        Route::get('/', [\App\Http\Controllers\ProjectController::class, 'index'])->name('projects.index');
        Route::get('/create', [\App\Http\Controllers\ProjectController::class, 'create'])->name('projects.create');
        Route::post('/', [\App\Http\Controllers\ProjectController::class, 'store'])->name('projects.store');
        Route::get('/{project}', [\App\Http\Controllers\ProjectController::class, 'show'])->name('projects.show');
        Route::get('/{project}/edit', [\App\Http\Controllers\ProjectController::class, 'edit'])->name('projects.edit');
        Route::patch('/{project}', [\App\Http\Controllers\ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/{project}', [\App\Http\Controllers\ProjectController::class, 'destroy'])->name('projects.destroy');
        Route::post('/{project}/add-member', [\App\Http\Controllers\ProjectController::class, 'addMember'])->name('projects.add-member');
        Route::delete('/{project}/remove-member/{user}', [\App\Http\Controllers\ProjectController::class, 'removeMember'])->name('projects.remove-member');
    });

    Route::get('/leaves', [LeaveController::class, 'index'])->name('leaves.index');
    Route::get('/leaves/{leave}/edit', [LeaveController::class, 'edit'])->name('leaves.edit');
    Route::put('/leaves/{leave}', [LeaveController::class, 'update'])->name('leaves.update');
    Route::post('/leaves', [LeaveController::class, 'store'])->name('leaves.store');
    Route::post('/Leave/approve', [\App\Http\Controllers\LeaveController::class, 'approve'])->name('leave.approve');
    Route::post('/Leave/refuse', [\App\Http\Controllers\LeaveController::class, 'refuse'])->name('leave.refuse');
    Route::post('/Leave/delete', [\App\Http\Controllers\LeaveController::class, 'delete'])->name('leave.delete');
    Route::post('/leave/cancel', [LeaveController::class, 'cancel'])->name('leave.cancel');
    // Leave routes
    // Route::get('/leaves', [LeaveController::class, 'index'])->name('leaves.index');
    // Route::post('/leaves', [LeaveController::class, 'store'])->name('leaves.store');
    // Route::post('/leaves', [LeaveController::class, 'cancel'])->name('leaves.cancel');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    // User management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/warning', [UserController::class, 'warning'])->name('users.warning');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

require __DIR__ . '/auth.php';
