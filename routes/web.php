<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Customer Frontstore Index (Home Page)
Route::get('/', function () {
    $categories = Category::withCount('menuItems')->get();
    $menuItems = MenuItem::with('category')->where('is_available', true)->get();

    return view('welcome', compact('categories', 'menuItems'));
})->name('home');

// Admin Protected Routes Group
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    
    // Admin Dashboard Route
    Route::get('/dashboard', function () {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return redirect()->route('home');
        }

        $totalSalesRevenue = Order::where('status', 'completed')->sum('total_amount');
        if ($totalSalesRevenue == 0) {
            $totalSalesRevenue = Order::sum('total_amount');
        }

        $activeOrdersCount = Order::whereIn('status', ['pending', 'preparing', 'delivering', 'confirmed'])->count();
        $pendingPreparationCount = Order::whereIn('status', ['pending', 'preparing'])->count();
        $totalFoodItems = MenuItem::count();
        $totalCategoriesCount = Category::count();
        $registeredCustomersCount = User::where('role', 'user')->count();
        $recentOrders = Order::with('user')->latest()->take(10)->get();

        return view('admin.dashboard.index', compact(
            'totalSalesRevenue',
            'activeOrdersCount',
            'pendingPreparationCount',
            'totalFoodItems',
            'totalCategoriesCount',
            'registeredCustomersCount',
            'recentOrders'
        ));
    })->name('dashboard');

    // Admin Resource Routes
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
    Route::resource('menuItems', MenuItemController::class)->except(['create', 'show', 'edit']);
    Route::resource('orders', OrderController::class)->except(['create', 'show', 'edit']);
    Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);
});

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
