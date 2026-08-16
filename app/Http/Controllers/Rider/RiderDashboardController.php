<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiderDashboardController extends Controller
{
    /**
     * Display the rider dashboard with active and past deliveries.
     */
    public function index()
    {
        /** @var \App\Models\User $rider */
        $rider = Auth::user();

        if (!$rider || !$rider->isRider()) {
            return redirect()->route('home')->with('error', 'Access denied. Rider accounts only.');
        }

        // Active Deliveries assigned to this rider only
        $activeDeliveries = Order::with(['user', 'orderItems.menuItem'])
            ->where('rider_id', $rider->id)
            ->whereIn('status', ['confirmed', 'preparing', 'delivering'])
            ->latest()
            ->get();

        // Completed Deliveries by this rider
        $completedDeliveries = Order::with(['user', 'orderItems.menuItem'])
            ->where('rider_id', $rider->id)
            ->where('status', 'completed')
            ->latest()
            ->take(20)
            ->get();

        // Quick Stats
        $stats = [
            'active_count' => $activeDeliveries->count(),
            'completed_today' => $completedDeliveries->filter(function ($o) {
                return $o->updated_at && $o->updated_at->isToday();
            })->count(),
            'total_earnings_today' => $completedDeliveries->filter(function ($o) {
                return $o->updated_at && $o->updated_at->isToday();
            })->sum('delivery_fee'),
        ];

        return view('rider.dashboard', compact('activeDeliveries', 'completedDeliveries', 'stats', 'rider'));
    }

    /**
     * Start delivery for an assigned order.
     */
    public function startDelivery(Order $order)
    {
        /** @var \App\Models\User $rider */
        $rider = Auth::user();
        if (!$rider || !$rider->isRider()) {
            return redirect()->route('home')->with('error', 'Access denied.');
        }

        $order->update([
            'rider_id' => $rider->id,
            'status' => 'delivering',
        ]);

        return back()->with('success', "Order #{$order->order_number} is now Out for Delivery! 🛵");
    }

    /**
     * Mark an order as completed & paid upon successful delivery.
     */
    public function completeDelivery(Order $order)
    {
        if ($order->status !== 'delivering') {
            return back()->with('error', 'Order must be picked up first before marking as delivered!');
        }

        /** @var \App\Models\User $rider */
        $rider = Auth::user();
        if (!$rider || !$rider->isRider()) {
            return redirect()->route('home')->with('error', 'Access denied.');
        }

        $order->update([
            'rider_id' => $rider->id,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        return back()->with('success', "Order #{$order->order_number} successfully Delivered & Payment Collected! 🎉💰");
    }
}
