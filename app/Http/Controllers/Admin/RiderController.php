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
     * Update the specified rider in storage.
     */
    public function update(Request $request, User $rider)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$rider->id],
            'phone_number' => ['required', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'city' => $request->city ?? 'Yangon',
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $rider->update($updateData);

        return redirect()->route('admin.riders.index')
            ->with('success', "Rider '{$rider->name}' updated successfully! 🛵");
    }

    /**
     * Remove the specified rider from storage.
     */
    public function destroy(User $rider)
    {
        // Unassign orders assigned to this rider so they aren't orphaned with invalid foreign key
        Order::where('rider_id', $rider->id)->update(['rider_id' => null]);

        $riderName = $rider->name;
        $rider->delete();

        return redirect()->route('admin.riders.index')
            ->with('success', "Rider '{$riderName}' deleted successfully! 🗑️");
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

