<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of orders in admin panel.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $paymentMethod = $request->query('payment_method');

        // Order Statistics
        $totalOrdersCount = Order::count();
        $pendingCount = Order::where('status', 'pending')->count();
        $activeCount = Order::whereIn('status', ['pending', 'preparing', 'delivering', 'confirmed'])->count();
        $completedCount = Order::where('status', 'completed')->count();
        $cancelledCount = Order::where('status', 'cancelled')->count();
        $totalRevenue = Order::where('status', 'completed')->sum('total_amount');
        if ($totalRevenue == 0) {
            $totalRevenue = Order::sum('total_amount');
        }

        $orders = Order::with(['user', 'rider', 'orderItems.menuItem'])
            ->when($search, function ($query, $search) {
                return $query->where('order_number', 'like', "%{$search}%")
                             ->orWhere('delivery_phone', 'like', "%{$search}%")
                             ->orWhere('delivery_address', 'like', "%{$search}%")
                             ->orWhereHas('user', function ($q) use ($search) {
                                 $q->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                             });
            })
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($paymentMethod, function ($query, $paymentMethod) {
                return $query->where('payment_method', $paymentMethod);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $riders = \App\Models\User::where('role', 'rider')->get();

        return view('admin.orders.index', compact(
            'orders',
            'riders',
            'search',
            'status',
            'paymentMethod',
            'totalOrdersCount',
            'pendingCount',
            'activeCount',
            'completedCount',
            'cancelledCount',
            'totalRevenue'
        ));
    }

    /**
     * Update order status or payment status.
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,confirmed,preparing,delivering,completed,cancelled',
            'payment_status' => 'nullable|string|in:paid,unpaid,pending_verification',
        ]);

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'completed') {
            $updateData['payment_status'] = 'paid';
        } elseif (isset($validated['payment_status'])) {
            $updateData['payment_status'] = $validated['payment_status'];
        }

        $order->update($updateData);

        if ($validated['status'] === 'completed') {
            $message = "Order #{$order->order_number} Completed & Payment marked as Paid! 💰✅";
        } else {
            $message = "Order #{$order->order_number} status updated to " . ucfirst($validated['status']) . " successfully!";
        }

        $returnUrl = $request->input('return_url') ?: url()->previous(route('admin.orders.index'));
        return redirect()->to($returnUrl)->with('success', $message);
    }

    /**
     * Delete specified order from storage.
     */
    public function destroy(Request $request, Order $order)
    {
        $orderNumber = $order->order_number;
        $order->orderItems()->delete();
        $order->delete();

        $returnUrl = $request->input('return_url') ?: url()->previous(route('admin.orders.index'));
        return redirect()->to($returnUrl)->with('success', "Order #{$orderNumber} deleted successfully!");
    }
}
