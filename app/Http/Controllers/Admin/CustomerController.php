<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Display a listing of registered customers in the admin panel.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status', 'all');

        // Customer Statistics in single aggregate query
        $stats = User::where('role', 'user')->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'active' OR status IS NULL THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'banned' THEN 1 ELSE 0 END) as banned
        ")->first();

        $totalCustomers = (int)($stats->total ?? 0);
        $activeCustomers = (int)($stats->active ?? 0);
        $bannedCustomers = (int)($stats->banned ?? 0);
        $totalCustomerOrders = Order::whereHas('user', function ($query) {
            $query->where('role', 'user');
        })->count();

        // Customer Query with order count and total spend
        $customers = User::where('role', 'user')
            ->withCount('orders')
            ->withSum('orders as total_spent', 'total_amount')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone_number', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%")
                      ->orWhere('detail_address', 'like', "%{$search}%");
                });
            })
            ->when($status === 'active', function ($query) {
                return $query->where(function ($q) {
                    $q->where('status', 'active')->orWhereNull('status');
                });
            })
            ->when($status === 'banned', function ($query) {
                return $query->where('status', 'banned');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.customers.index', compact(
            'customers',
            'search',
            'status',
            'totalCustomers',
            'activeCustomers',
            'bannedCustomers',
            'totalCustomerOrders'
        ));
    }

    /**
     * Toggle Customer Ban / Unban status.
     */
    public function toggleStatus(Request $request, User $customer)
    {
        $returnUrl = $request->input('return_url') ?: url()->previous(route('admin.customers.index'));

        if ($customer->id === Auth::id()) {
            return redirect()->to($returnUrl)->with('error', 'You cannot modify status on your own logged-in account!');
        }

        if ($customer->role !== 'user') {
            return redirect()->to($returnUrl)->with('error', 'Only customer accounts can be banned or unbanned from here.');
        }

        $newStatus = ($customer->status === 'banned') ? 'active' : 'banned';
        $customer->update(['status' => $newStatus]);

        if ($newStatus === 'banned') {
            $msg = "Customer '{$customer->name}' has been banned! 🚫 (Account suspended)";
        } else {
            $msg = "Customer '{$customer->name}' has been unbanned! ✅ (Account is now active)";
        }

        return redirect()->to($returnUrl)->with('success', $msg);
    }
}
