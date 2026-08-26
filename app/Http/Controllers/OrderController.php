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
        $paymentStatus = $request->query('payment_status');
        $riderId = $request->query('rider_id');
        $dateRange = $request->query('date_range');
        $sortBy = $request->query('sort_by', 'latest');

        // Order Statistics in a single aggregate query
        $stats = Order::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status IN ('pending', 'preparing', 'delivering', 'confirmed') THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
            SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as completed_revenue,
            SUM(total_amount) as total_revenue
        ")->first();

        $totalOrdersCount = (int)($stats->total ?? 0);
        $pendingCount = (int)($stats->pending ?? 0);
        $activeCount = (int)($stats->active ?? 0);
        $completedCount = (int)($stats->completed ?? 0);
        $cancelledCount = (int)($stats->cancelled ?? 0);
        $totalRevenue = (float)(($stats->completed_revenue ?? 0) > 0 ? $stats->completed_revenue : ($stats->total_revenue ?? 0));

        $ordersQuery = Order::with(['user', 'rider', 'orderItems.menuItem'])
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
            ->when($paymentStatus, function ($query, $paymentStatus) {
                return $query->where('payment_status', $paymentStatus);
            })
            ->when($riderId !== null && $riderId !== '', function ($query) use ($riderId) {
                if ($riderId === 'unassigned') {
                    return $query->whereNull('rider_id');
                }
                return $query->where('rider_id', $riderId);
            })
            ->when($dateRange, function ($query, $dateRange) {
                if ($dateRange === 'today') {
                    return $query->whereDate('created_at', today());
                } elseif ($dateRange === 'yesterday') {
                    return $query->whereDate('created_at', today()->subDay());
                } elseif ($dateRange === 'this_week') {
                    return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                } elseif ($dateRange === 'this_month') {
                    return $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                }
                return $query;
            });

        if ($sortBy === 'oldest') {
            $ordersQuery->oldest();
        } elseif ($sortBy === 'amount_high') {
            $ordersQuery->orderByDesc('total_amount');
        } elseif ($sortBy === 'amount_low') {
            $ordersQuery->orderBy('total_amount');
        } else {
            $ordersQuery->latest();
        }

        $orders = $ordersQuery->paginate(10)->withQueryString();
        $riders = \App\Models\User::where('role', 'rider')->get();

        return view('admin.orders.index', compact(
            'orders',
            'riders',
            'search',
            'status',
            'paymentMethod',
            'paymentStatus',
            'riderId',
            'dateRange',
            'sortBy',
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
            'status' => 'nullable|string|in:pending,confirmed,preparing,delivering,completed,cancelled',
            'payment_status' => 'nullable|string|in:paid,unpaid,pending_verification',
        ]);

        $updateData = [];
        if (!empty($validated['status'])) {
            $updateData['status'] = $validated['status'];
        }

        if (isset($validated['status']) && $validated['status'] === 'completed') {
            $updateData['payment_status'] = 'paid';
        } elseif (isset($validated['payment_status'])) {
            $updateData['payment_status'] = $validated['payment_status'];
        }

        if (!empty($updateData)) {
            $order->update($updateData);

            if (($validated['status'] ?? null) === 'confirmed' || (($validated['payment_status'] ?? null) === 'paid' && $order->status === 'confirmed')) {
                \App\Services\PayslipService::sendOrderAcceptedPayslips($order);
            }
        }

        if (($validated['status'] ?? null) === 'completed') {
            $message = "Order #{$order->order_number} Completed & Payment marked as Paid! 💰✅";
        } elseif (($validated['payment_status'] ?? null) === 'paid' && ($validated['status'] ?? null) === 'confirmed') {
            $message = "Order #{$order->order_number} Payment Approved & Digital Order Slip generated! 🧾🎉";
        } elseif (isset($validated['payment_status']) && empty($validated['status'])) {
            $message = "Order #{$order->order_number} payment status updated to " . strtoupper(str_replace('_', ' ', $validated['payment_status'])) . " successfully!";
        } else {
            $message = "Order #{$order->order_number} updated successfully!";
        }

        $returnUrl = $request->input('return_url') ?: url()->previous(route('admin.orders.index'));
        return redirect()->to($returnUrl)->with('success', $message);
    }
}
