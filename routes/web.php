<?php

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
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

// Language Switcher Route
Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'my'])) {
        session()->put('locale', $locale);
        return back()->withCookie(cookie()->forever('locale', $locale));
    }
    return back();
})->name('lang.switch');

// Cart Page
Route::get('/cart', function () {
    return view('cart');
})->name('cart');

// Customer Order Routes (auth required)
Route::middleware('auth')->group(function () {
    // Shared Order Handling Logic Group
    Route::prefix('customer')->name('customer.')->group(function () {
        // Place order from cart
        Route::post('/orders', function (\Illuminate\Http\Request $request) {
            /** @var \App\Models\User $authUser */
            $authUser = Auth::user();
            if ($authUser && $authUser->isBanned()) {
                return redirect()->route('cart')->with('error', 'Your customer account has been suspended/banned from placing orders. Please contact support.');
            }

            $request->validate([
                'delivery_address'   => 'required|string|max:500',
                'delivery_phone'     => ['required', 'string', 'max:30', 'regex:/^(\+?95\s?9|\+?959|09|9)[0-9]{6,9}$/'],
                'payment_method'     => 'required|in:cod,kbzpay,wavepay',
                'cart_items'         => 'required|string',
                'total_amount'       => 'required|numeric|min:1',
                'payment_screenshot' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
                'region_type'        => 'nullable|string',
                'delivery_township'  => 'nullable|string',
            ], [
                'delivery_phone.regex' => 'Please provide a valid Myanmar phone number starting with +95 9... (တရားဝင် မြန်မာဖုန်းနံပါတ် +95 9... ထည့်သွင်းပေးပါ)',
            ]);

            // Normalize delivery phone to clean +95 9 format
            $rawDigits = preg_replace('/\D/', '', $request->delivery_phone);
            if (str_starts_with($rawDigits, '959')) {
                $formattedPhone = '+95 9' . substr($rawDigits, 3);
            } elseif (str_starts_with($rawDigits, '09')) {
                $formattedPhone = '+95 9' . substr($rawDigits, 2);
            } elseif (str_starts_with($rawDigits, '9') && strlen($rawDigits) >= 8) {
                $formattedPhone = '+95 9' . substr($rawDigits, 1);
            } else {
                $formattedPhone = '+95 9' . $rawDigits;
            }

            $cartItems = json_decode($request->cart_items, true);
            if (empty($cartItems)) {
                return redirect()->route('cart')->with('error', 'Your cart is empty.');
            }

            // Validate backend stock for every cart item
            foreach ($cartItems as $cartItem) {
                $menuItem = MenuItem::find($cartItem['id']);
                if (!$menuItem || !$menuItem->is_available) {
                    return redirect()->route('cart')->with('error', "Item '" . ($cartItem['name'] ?? 'Item') . "' is currently unavailable.");
                }
                if ($menuItem->stock < $cartItem['qty']) {
                    return redirect()->route('cart')->with('error', "Sorry! Cannot place order because '{$menuItem->name}' has only {$menuItem->stock} unit(s) available in stock (you requested {$cartItem['qty']}). Please adjust your quantity.");
                }
            }

            $screenshotPath = null;
            if ($request->hasFile('payment_screenshot')) {
                $file = $request->file('payment_screenshot');
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/payments'), $fileName);
                $screenshotPath = 'uploads/payments/' . $fileName;
            }

            // Prevent duplicate order creation if identical order was submitted in the last 15 seconds
            $existingRecentOrder = Order::where('user_id', Auth::id())
                ->where('total_amount', $request->total_amount)
                ->where('delivery_address', $request->delivery_address)
                ->where('created_at', '>=', now()->subSeconds(15))
                ->latest()
                ->first();

            if ($existingRecentOrder) {
                return redirect()->route('customer.orders.show', $existingRecentOrder)
                    ->with('success', "Order #{$existingRecentOrder->order_number} placed successfully! 🎉");
            }

            $order = Order::create([
                'order_number'       => 'ORD-' . strtoupper(uniqid()),
                'user_id'            => Auth::id(),
                'total_amount'       => $request->total_amount,
                'delivery_fee'       => $request->delivery_fee ?? 0,
                'tax_amount'         => $request->tax_amount ?? 0,
                'delivery_address'   => $request->delivery_address,
                'region_type'        => $request->region_type ?? 'yangon',
                'delivery_township'  => $request->delivery_township,
                'delivery_phone'     => $formattedPhone,
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

                    // Decrement stock in database
                    $menuItem->decrement('stock', $cartItem['qty']);
                    if ($menuItem->stock <= 0) {
                        $menuItem->update(['stock' => 0, 'is_available' => false]);
                    }
                }
            }

            return redirect()->route('customer.orders.show', $order)
                ->with('success', "Order #{$order->order_number} placed successfully! 🎉");
        })->name('orders.store');

        // Customer Orders History List (My Orders)
        Route::get('/orders', function () {
            $orders = Order::where('user_id', Auth::id())
                ->with(['orderItems.menuItem'])
                ->latest()
                ->get();
            return view('user.orders.index', compact('orders'));
        })->name('orders.index');

        // Order Status JSON endpoint for real-time polling
        Route::get('/orders/{order}/json-status', function (Order $order) {
            if ($order->user_id !== Auth::id() && (!Auth::user()->isAdmin())) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return response()->json([
                'status'               => $order->status,
                'payment_status'       => $order->payment_status,
                'notes'                => $order->notes,
                'rider_id'             => $order->rider_id,
                'rider_name'           => $order->rider ? $order->rider->name : null,
                'rider_phone'          => $order->rider ? ($order->rider->phone_number ?? $order->rider->phone ?? null) : null,
                'delivery_proof_photo' => $order->delivery_proof_photo ? asset($order->delivery_proof_photo) : null,
            ]);
        })->name('orders.json_status');

        // Order Detail (tracking)
        Route::get('/orders/{order}', function (Order $order) {
            if ($order->user_id !== Auth::id() && (!Auth::user()->isAdmin())) {
                abort(403);
            }
            $order->load(['orderItems.menuItem', 'rider']);
            return view('user.orders.show', compact('order'));
        })->name('orders.show');

        // Foodpanda-styled Printable Payslip & Invoice View
        Route::get('/orders/{order}/payslip', function (Order $order) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if (!$user) {
                return redirect()->route('login');
            }
            if (!$user->isAdmin() && $order->user_id !== $user->id && $order->rider_id !== $user->id) {
                abort(403, 'Unauthorized access to this order payslip.');
            }
            if (!$user->isAdmin() && $order->status === 'pending') {
                return redirect()->route('customer.orders.show', $order)
                    ->with('error', 'Admin မှ အတည်မပြုရသေးပါသဖြင့် ဒစ်ဂျစ်တယ်ပြေစာ မထုတ်ပေးသေးပါ (Digital Order Slip is generated only after Admin approves the order).');
            }
            $order->loadMissing(['orderItems.menuItem.category', 'user', 'rider']);
            return view('orders.payslip', compact('order'));
        })->name('orders.payslip');

        // Customer upload or update payslip on existing order
        Route::post('/orders/{order}/upload-payslip', function (\Illuminate\Http\Request $request, Order $order) {
            if ($order->user_id !== Auth::id() && (!Auth::user()->isAdmin())) {
                abort(403);
            }

            $request->validate([
                'payment_screenshot' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            ]);

            if ($request->hasFile('payment_screenshot')) {
                $file = $request->file('payment_screenshot');
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/payments'), $fileName);
                $screenshotPath = 'uploads/payments/' . $fileName;

                $order->update([
                    'payment_screenshot' => $screenshotPath,
                    'payment_status'     => 'pending_verification',
                ]);
            }

            return redirect()->route('customer.orders.show', $order)
                ->with('success', 'Payment payslip uploaded successfully! Our team will verify it shortly. 🎉');
        })->name('orders.upload_payslip');

        // Order Real-Time Chat Message Endpoints (Customer named route)
        Route::get('/orders/{order}/messages', [\App\Http\Controllers\OrderMessageController::class, 'index'])->name('orders.messages.index');
        Route::post('/orders/{order}/messages', [\App\Http\Controllers\OrderMessageController::class, 'store'])->name('orders.messages.store');

        // Customer Help & Complaints System
        Route::get('/help', [\App\Http\Controllers\Customer\ComplaintController::class, 'index'])->name('help');
        Route::resource('complaints', \App\Http\Controllers\Customer\ComplaintController::class)->only(['index', 'create', 'store', 'show']);
    });

    // Public Help redirect for authenticated users
    Route::get('/help', function () {
        return redirect()->route('customer.help');
    })->name('help');

    // Global Order Chat Endpoints for Authenticated Riders, Customers, Admins
    Route::get('/orders/{order}/messages', [\App\Http\Controllers\OrderMessageController::class, 'index'])->name('orders.messages.index');
    Route::post('/orders/{order}/messages', [\App\Http\Controllers\OrderMessageController::class, 'store'])->name('orders.messages.store');

    // Global Foodpanda Order Payslip Route
    Route::get('/orders/{order}/payslip', function (Order $order) {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        if (!$user->isAdmin() && $order->user_id !== $user->id && $order->rider_id !== $user->id) {
            abort(403, 'Unauthorized access to this order payslip.');
        }
        if (!$user->isAdmin() && $order->status === 'pending') {
            return redirect()->route('customer.orders.show', $order)
                ->with('error', 'Admin မှ အတည်မပြုရသေးပါသဖြင့် ဒစ်ဂျစ်တယ်ပြေစာ မထုတ်ပေးသေးပါ (Digital Order Slip is generated only after Admin approves the order).');
        }
        $order->loadMissing(['orderItems.menuItem.category', 'user', 'rider']);
        return view('orders.payslip', compact('order'));
    })->name('orders.payslip');

    // Legacy user.* aliases for backwards compatibility
    Route::prefix('user')->name('user.')->group(function () {
        Route::post('/orders', fn(\Illuminate\Http\Request $r) => redirect()->route('customer.orders.store'))->name('orders.store');
        Route::get('/orders', fn() => redirect()->route('customer.orders.index'))->name('orders.index');
        Route::get('/orders/{order}/json-status', fn(Order $order) => redirect()->route('customer.orders.json_status', $order))->name('orders.json_status');
        Route::get('/orders/{order}', fn(Order $order) => redirect()->route('customer.orders.show', $order))->name('orders.show');
        Route::get('/orders/{order}/payslip', fn(Order $order) => redirect()->route('orders.payslip', $order))->name('orders.payslip');
    });
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

        $todayDate = today()->toDateString();
        $stats = Order::selectRaw("
            COUNT(*) as total_orders,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
            SUM(CASE WHEN DATE(created_at) = ? AND status = 'completed' THEN total_amount ELSE 0 END) as today_completed_revenue,
            SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as all_completed_revenue,
            SUM(total_amount) as total_revenue,
            SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as today_orders,
            SUM(CASE WHEN status IN ('pending', 'preparing') THEN 1 ELSE 0 END) as pending_orders,
            SUM(CASE WHEN status IN ('pending', 'preparing', 'delivering', 'confirmed') THEN 1 ELSE 0 END) as active_orders
        ", [$todayDate, $todayDate])->first();

        $totalOrdersCount = (int)($stats->total_orders ?? 0);
        $cancelledCount = (int)($stats->cancelled_orders ?? 0);
        $cancellationRate = $totalOrdersCount > 0 ? round(($cancelledCount / $totalOrdersCount) * 100, 1) : 0;

        $todaysRevenue = (float)(($stats->today_completed_revenue ?? 0) > 0 ? $stats->today_completed_revenue : (($stats->all_completed_revenue ?? 0) > 0 ? $stats->all_completed_revenue : ($stats->total_revenue ?? 0)));
        $todaysOrdersCount = (int)(($stats->today_orders ?? 0) > 0 ? $stats->today_orders : $totalOrdersCount);
        $pendingOrdersCount = (int)($stats->pending_orders ?? 0);
        $activeOrdersCount = (int)($stats->active_orders ?? 0);

        $recentOrders = Order::with(['user', 'orderItems.menuItem'])
            ->latest()
            ->take(15)
            ->get();

        return view('admin.dashboard.index', compact(
            'todaysRevenue',
            'todaysOrdersCount',
            'pendingOrdersCount',
            'cancellationRate',
            'activeOrdersCount',
            'recentOrders'
        ));
    })->name('dashboard');

    // Quick Action Endpoint: Accept Order & Generate Foodpanda Payslips
    Route::post('/orders/{order}/accept', function (Order $order) {
        $isOnlinePay = in_array($order->payment_method, ['kbzpay', 'wavepay']);
        $updateData = [
            'status' => 'confirmed',
            'updated_at' => now(),
        ];
        if ($isOnlinePay) {
            $updateData['payment_status'] = 'paid';
        } else {
            $updateData['payment_status'] = 'unpaid';
        }
        $order->update($updateData);

        // Generate & Email Official Foodpanda Payslips to Customer & Rider
        $result = \App\Services\PayslipService::sendOrderAcceptedPayslips($order);

        $customerEmail = $order->user->email ?? 'customer';
        $riderMsg = $order->rider ? " & Rider ({$order->rider->email})" : " (Available in Rider Pickup Pool)";

        if ($isOnlinePay) {
            $flashMsg = "Order #{$order->order_number} Online Payment Approved! 🧾 Digital Slip with PAID stamp generated & dispatched to Kitchen and Rider App ({$customerEmail}){$riderMsg} 🎉";
        } else {
            $flashMsg = "Order #{$order->order_number} Confirmed for Kitchen! 💵 Cash on Delivery (" . number_format($order->total_amount) . " MMK to be collected by Rider). Digital receipt will issue upon rider cash confirmation. 🎉";
        }

        return back()->with('success', $flashMsg);
    })->name('orders.accept');

    // Admin Action: Manual Generate / Resend Payslip Emails
    Route::post('/orders/{order}/send-payslip', function (Illuminate\Http\Request $request, Order $order) {
        $target = $request->input('recipient', 'both'); // 'customer', 'rider', 'both'
        $sentInfo = [];

        if (in_array($target, ['customer', 'both'])) {
            $cOk = \App\Services\PayslipService::sendCustomerPayslip($order);
            if ($cOk) {
                $sentInfo[] = "Customer ({$order->user->email})";
            }
        }

        if (in_array($target, ['rider', 'both'])) {
            if ($order->rider) {
                $rOk = \App\Services\PayslipService::sendRiderPayslip($order);
                if ($rOk) {
                    $sentInfo[] = "Rider ({$order->rider->email})";
                }
            } else {
                $sentInfo[] = "No rider assigned yet";
            }
        }

        $msg = !empty($sentInfo) 
            ? "Foodpanda Payslip emailed successfully to: " . implode(', ', $sentInfo) . " 📧🧾"
            : "Could not send payslip emails. Please check customer/rider email settings.";

        return back()->with('success', $msg);
    })->name('orders.send_payslip');

    // Quick Action Endpoint: Reject Order
    Route::post('/orders/{order}/reject', function (Illuminate\Http\Request $request, Order $order) {
        $reason = $request->input('reason', 'Kitchen Busy');
        $order->update([
            'status' => 'cancelled',
            'notes' => 'Rejected by Admin: ' . $reason
        ]);
        return back()->with('success', "Order #{$order->order_number} Rejected ({$reason}) ❌");
    })->name('orders.reject');

    // Admin Inventory Management Routes
    Route::get('/inventory', [\App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/{menuItem}/toggle-stock', [\App\Http\Controllers\Admin\InventoryController::class, 'toggleStock'])->name('inventory.toggle-stock');
    Route::post('/inventory/{menuItem}/update-stock', [\App\Http\Controllers\Admin\InventoryController::class, 'updateStock'])->name('inventory.update-stock');
    Route::post('/inventory/bulk-restock', [\App\Http\Controllers\Admin\InventoryController::class, 'bulkRestock'])->name('inventory.bulk-restock');

    // Quick Action Endpoint: Toggle Stock Availability Switch (kept for backwards compatibility)
    Route::post('/menuItems/{menuItem}/toggle-stock', [\App\Http\Controllers\Admin\InventoryController::class, 'toggleStock'])->name('menuItems.toggle-stock');

    // Admin Rider Management Routes
    Route::get('/riders', [\App\Http\Controllers\Admin\RiderController::class, 'index'])->name('riders.index');
    Route::post('/riders', [\App\Http\Controllers\Admin\RiderController::class, 'store'])->name('riders.store');
    Route::put('/riders/{rider}', [\App\Http\Controllers\Admin\RiderController::class, 'update'])->name('riders.update');
    Route::delete('/riders/{rider}', [\App\Http\Controllers\Admin\RiderController::class, 'destroy'])->name('riders.destroy');
    Route::post('/orders/{order}/assign-rider', [\App\Http\Controllers\Admin\RiderController::class, 'assignRider'])->name('orders.assignRider');
    
    // JSON Endpoint for Admin Real-Time Status Polling
    Route::get('/orders/json-list', function () {
        $orders = Order::select('id', 'order_number', 'status', 'payment_status', 'rider_id', 'updated_at')
            ->latest()
            ->take(30)
            ->get();
        return response()->json(['orders' => $orders]);
    })->name('orders.json_list');

    // Admin Resource Routes
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
    Route::resource('menuItems', MenuItemController::class)->except(['create', 'show', 'edit']);
    Route::resource('orders', OrderController::class)->except(['create', 'show', 'edit', 'destroy']);
    Route::resource('orderItems', OrderItemController::class)->except(['create', 'show', 'edit']);
    // Admin Customer Routes (Ban/Unban status management only)
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
    Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');

    // Admin Customer Complaints Management Routes
    Route::resource('complaints', \App\Http\Controllers\Admin\ComplaintController::class)->only(['index', 'show', 'update', 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Rider Portal Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('rider')->as('rider.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Rider\RiderDashboardController::class, 'index'])->name('dashboard');
    Route::post('/orders/{order}/pickup', [\App\Http\Controllers\Rider\RiderDashboardController::class, 'pickup'])->name('orders.pickup');
    Route::post('/orders/{order}/start-delivery', [\App\Http\Controllers\Rider\RiderDashboardController::class, 'startDelivery'])->name('orders.start');
    Route::post('/orders/{order}/complete-delivery', [\App\Http\Controllers\Rider\RiderDashboardController::class, 'completeDelivery'])->name('orders.complete');
    Route::get('/messages/notifications', [\App\Http\Controllers\OrderMessageController::class, 'riderNotifications'])->name('messages.notifications');
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
