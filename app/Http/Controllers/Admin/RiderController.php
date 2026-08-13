<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RiderController extends Controller
{
    /**
     * Display a listing of all riders.
     */
    public function index()
    {
        $riders = User::where('role', 'rider')
            ->withCount([
                'assignedDeliveries as active_deliveries_count' => function ($query) {
                    $query->whereIn('status', ['confirmed', 'preparing', 'delivering']);
                },
                'assignedDeliveries as completed_deliveries_count' => function ($query) {
                    $query->where('status', 'completed');
                }
            ])
            ->latest()
            ->paginate(15);

        return view('admin.riders.index', compact('riders'));
    }

    /**
     * Store a newly created rider in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone_number' => ['required', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:100'],
            'password' => ['required', Rules\Password::defaults()],
        ]);

        $rider = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'city' => $request->city ?? 'Yangon',
            'role' => 'rider',
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.riders.index')
            ->with('success', "Rider '{$rider->name}' created successfully! 🛵");
    }

    /**
     * Assign a rider to an order.
     */
    public function assignRider(Request $request, Order $order)
    {
        $request->validate([
            'rider_id' => ['nullable', 'exists:users,id'],
        ]);

        $riderId = $request->input('rider_id');
        
        $order->update([
            'rider_id' => $riderId ?: null
        ]);

        $riderName = $order->rider ? $order->rider->name : 'Unassigned';

        return back()->with('success', "Order #{$order->order_number} assigned to {$riderName}! 📦");
    }
}
