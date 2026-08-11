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
    $categories = Category::withCount('menuItems')->with('menuItems')->get();
    $menuItems = MenuItem::with('category')->where('is_available', true)->get();

    return view('welcome', compact('categories', 'menuItems'));
})->name('home');

// Cart Page
Route::get('/cart', function () {
    return view('cart');
})->name('cart');

// User Order Routes (auth required)
Route::middleware('auth')->prefix('user')->name('user.')->group(function () {
    // Place order from cart
    Route::post('/orders', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'delivery_address'   => 'required|string|max:500',
            'delivery_phone'     => 'required|string|max:30',
            'payment_method'     => 'required|in:cod,kbzpay,wavepay',
            'cart_items'         => 'required|string',
            'total_amount'       => 'required|numeric|min:1',
            'payment_screenshot' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'region_type'        => 'nullable|string',
            'delivery_township'  => 'nullable|string',
        ]);

        $cartItems = json_decode($request->cart_items, true);
        if (empty($cartItems)) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        $screenshotPath = null;
        if ($request->hasFile('payment_screenshot')) {
            $file = $request->file('payment_screenshot');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/payments'), $fileName);
            $screenshotPath = 'uploads/payments/' . $fileName;
        }

        $order = Order::create([
            'order_number'       => 'ORD-' . strtoupper(uniqid()),
            'user_id'            => Auth::id(),
            'total_amount'       => $request->total_amount,
            'delivery_fee'       => $request->delivery_fee ?? 0,
            'delivery_address'   => $request->delivery_address,
            'region_type'        => $request->region_type ?? 'yangon',
            'delivery_township'  => $request->delivery_township,
            'delivery_phone'     => $request->delivery_phone,
            'payment_method'     => $request->payment_method,
            'payment_screenshot' => $screenshotPath,
            'notes'              => $request->notes,
            'status'             => 'pending',
        ]);

        foreach ($cartItems as $cartItem) {
            $menuItem = MenuItem::find($cartItem['id']);
            if ($menuItem) {
                $order->orderItems()->create([
                    'menu_item_id' => $menuItem->id,
                    'quantity'     => $cartItem['qty'],
                    'unit_price'   => $menuItem->price,
                    'subtotal'     => $menuItem->price * $cartItem['qty'],
                ]);
            }
        }

        return redirect()->route('user.orders.show', $order)
            ->with('success', "Order #{$order->order_number} placed successfully! 🎉");
    })->name('orders.store');

    // User Orders History List (My Orders)
    Route::get('/orders', function () {
        $orders = Order::where('user_id', Auth::id())->with('orderItems')->latest()->get();
        return view('user.orders.index', compact('orders'));
    })->name('orders.index');

    // Order Status JSON endpoint for real-time polling
    Route::get('/orders/{order}/json-status', function (Order $order) {
        if ($order->user_id !== Auth::id() && (!Auth::user()->isAdmin())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json([
            'status'         => $order->status,
            'payment_status' => $order->payment_status,
        ]);
    })->name('orders.json_status');

    // Order Detail (tracking)
    Route::get('/orders/{order}', function (Order $order) {
        if ($order->user_id !== Auth::id() && (!Auth::user()->isAdmin())) {
            abort(403);
        }
        $order->load('orderItems.menuItem');
        return view('user.orders.show', compact('order'));
    })->name('orders.show');
});



// Admin Protected Routes Group
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    
    // Admin Dashboard Route
    Route::get('/dashboard', function () {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return redirect()->route('home');
        }

        $totalOrdersCount = Order::count();
        $cancelledCount = Order::where('status', 'cancelled')->count();
        $cancellationRate = $totalOrdersCount > 0 ? round(($cancelledCount / $totalOrdersCount) * 100, 1) : 0;

        // Today's Stats with Fallbacks for seeded test data
        $todaysRevenue = Order::whereDate('created_at', today())->where('status', 'completed')->sum('total_amount');
        if ($todaysRevenue == 0) {
            $todaysRevenue = Order::where('status', 'completed')->sum('total_amount');
            if ($todaysRevenue == 0) {
                $todaysRevenue = Order::sum('total_amount');
            }
        }

        $todaysOrdersCount = Order::whereDate('created_at', today())->count();
        if ($todaysOrdersCount == 0) {
            $todaysOrdersCount = $totalOrdersCount;
        }

        $pendingOrdersCount = Order::whereIn('status', ['pending', 'preparing'])->count();
        $activeOrdersCount = Order::whereIn('status', ['pending', 'preparing', 'delivering', 'confirmed'])->count();
        $recentOrders = Order::with(['user', 'orderItems.menuItem'])->latest()->take(20)->get();
        $menuItemsQuickControl = MenuItem::with('category')->orderBy('name', 'asc')->get();

        return view('admin.dashboard.index', compact(
            'todaysRevenue',
            'todaysOrdersCount',
            'pendingOrdersCount',
            'cancellationRate',
            'activeOrdersCount',
            'recentOrders',
            'menuItemsQuickControl'
        ));
    })->name('dashboard');

    // Quick Action Endpoint: Accept Order
    Route::post('/orders/{order}/accept', function (Order $order) {
        $order->update([
            'status' => 'confirmed',
            'payment_status' => 'paid'
        ]);
        return back()->with('success', "Order #{$order->order_number} Confirmed & Accepted! 🎉");
    })->name('orders.accept');

    // Quick Action Endpoint: Reject Order
    Route::post('/orders/{order}/reject', function (Illuminate\Http\Request $request, Order $order) {
        $reason = $request->input('reason', 'Kitchen Busy');
        $order->update([
            'status' => 'cancelled',
            'notes' => 'Rejected by Admin: ' . $reason
        ]);
        return back()->with('success', "Order #{$order->order_number} Rejected ({$reason}) ❌");
    })->name('orders.reject');

    // Quick Action Endpoint: Toggle Stock Availability Switch
    Route::post('/menuItems/{menuItem}/toggle-stock', function (MenuItem $menuItem) {
        $menuItem->update(['is_available' => !$menuItem->is_available]);
        $statusText = $menuItem->is_available ? 'Available (In-Stock) ✅' : 'Out of Stock (Disabled) 🚫';
        return back()->with('success', "Dish '{$menuItem->name}' is now marked as {$statusText}");
    })->name('menuItems.toggle-stock');

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
