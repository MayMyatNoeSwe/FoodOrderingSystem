<?php

namespace App\Http\Controllers\ShopOwner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ShopDashboardController extends Controller
{
    /**
     * Show the shop owner dashboard.
     */
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $shop = $user->ownedShop()->withCount(['menuItems', 'orders'])->first();

        if (!$shop) {
            return view('shop_owner.no_shop');
        }

        $recentOrders = Order::where('shop_id', $shop->id)
            ->with(['user', 'orderItems.menuItem'])
            ->latest()
            ->take(10)
            ->get();

        $stats = Order::where('shop_id', $shop->id)
            ->selectRaw("
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as total_revenue,
                SUM(CASE WHEN status IN ('pending', 'confirmed', 'preparing') THEN 1 ELSE 0 END) as active_orders,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders
            ")
            ->first();

        return view('shop_owner.dashboard.index', compact('shop', 'recentOrders', 'stats'));
    }
}
