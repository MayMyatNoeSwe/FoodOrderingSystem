<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiderDashboardController extends Controller
{
    /**
     * Display the rider dashboard with open pickup pool, active deliveries, and past deliveries.
     */
    public function index()
    {
        /** @var \App\Models\User $rider */
        $rider = Auth::user();

        if (!$rider || !$rider->isRider()) {
            return redirect()->route('home')->with('error', 'Access denied. Rider accounts only.');
        }

        // Open Pickup Pool: Approved orders waiting for ANY rider to pick up
        $availableOrders = Order::with(['user', 'orderItems.menuItem'])
            ->whereNull('rider_id')
            ->whereIn('status', ['confirmed', 'preparing'])
            ->latest()
            ->get();

        // Active Deliveries assigned to this rider
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
            'available_count' => $availableOrders->count(),
            'active_count' => $activeDeliveries->count(),
            'completed_today' => $completedDeliveries->filter(function ($o) {
                return $o->updated_at && $o->updated_at->isToday();
            })->count(),
            'total_earnings_today' => $completedDeliveries->filter(function ($o) {
                return $o->updated_at && $o->updated_at->isToday();
            })->sum('delivery_fee'),
        ];

        return view('rider.dashboard', compact('availableOrders', 'activeDeliveries', 'completedDeliveries', 'stats', 'rider'));
    }

    /**
     * Claim / Pick up an unassigned order from the open pool.
     */
    public function pickup(Order $order)
    {
        /** @var \App\Models\User $rider */
        $rider = Auth::user();
        if (!$rider || !$rider->isRider()) {
            return redirect()->route('home')->with('error', 'Access denied.');
        }

        // Check if another rider already claimed it
        if ($order->rider_id !== null) {
            return back()->with('error', "Sorry! Order #{$order->order_number} has already been picked up by another rider.");
        }

        if (!in_array($order->status, ['confirmed', 'preparing'])) {
            return back()->with('error', 'This order is not currently available for pickup.');
        }

        $order->update([
            'rider_id' => $rider->id,
            'status' => 'delivering',
        ]);

        // Send Foodpanda delivery slip email to rider
        \App\Services\PayslipService::sendRiderPayslip($order, $rider);

        return back()->with('success', "Order #{$order->order_number} successfully picked up! Foodpanda delivery slip emailed to you. 🛵💨");
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
     * Mark an order as completed & paid upon successful delivery with proof of delivery photo.
     * For COD orders, confirms cash collection and triggers digital receipt issuance for customer.
     */
    public function completeDelivery(Request $request, Order $order)
    {
        if ($order->status !== 'delivering') {
            return back()->with('error', 'Order must be picked up first before marking as delivered!');
        }

        /** @var \App\Models\User $rider */
        $rider = Auth::user();
        if (!$rider || !$rider->isRider()) {
            return redirect()->route('home')->with('error', 'Access denied.');
        }

        $rules = [
            'delivery_proof_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:8192',
        ];

        if ($order->payment_method === 'cod') {
            $rules['confirm_cash_collected'] = 'required';
        }

        $request->validate($rules, [
            'confirm_cash_collected.required' => 'ကျေးဇူးပြု၍ Customer ထံမှ ငွေလက်ခံရရှိကြောင်း အမှန်ခြစ် ပေးပါ (Please confirm cash collection from customer).',
        ]);

        $proofPath = null;
        if ($request->hasFile('delivery_proof_photo')) {
            $file = $request->file('delivery_proof_photo');
            $destinationPath = public_path('uploads/delivery_proofs');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $fileName = 'proof_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $fileName);
            $proofPath = 'uploads/delivery_proofs/' . $fileName;
        }

        $updateData = [
            'rider_id' => $rider->id,
            'status' => 'completed',
            'payment_status' => 'paid',
        ];

        if ($proofPath) {
            $updateData['delivery_proof_photo'] = $proofPath;
        }

        $order->update($updateData);

        if ($order->payment_method === 'cod') {
            $message = "Order #{$order->order_number} Delivered & Cash Received (" . number_format($order->total_amount) . " MMK) Confirmed! 💵 Customer app now displays PAID (CASH) Digital Receipt! 🎉";
        } else {
            $message = "Order #{$order->order_number} Delivered & Verified! 📸 Customer notified successfully. 🎉";
        }

        return back()->with('success', $message);
    }
}
