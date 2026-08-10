<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Customer Frontstore Index (Home Page)
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Admin Dashboard Route
Route::middleware(['auth'])->get('/admin/dashboard', function () {
    /** @var \App\Models\User $user */
    $user = Auth::user();
    if (!$user || !$user->isAdmin()) {
        return redirect()->route('home');
    }
    return view('admin.dashboard.index');
})->name('admin.dashboard');

// Dashboard Redirect Handler (Breeze Default Route)
Route::get('/dashboard', function () {
    /** @var \App\Models\User $user */
    $user = Auth::user();
    if ($user && $user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('home');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile Management Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
