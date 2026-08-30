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
    public function index(Request $request)
    {
        $search = $request->query('search');
        $city = $request->query('city');
        $sortBy = $request->query('sort_by', 'latest');
        $status = $request->query('status', 'all');

        $cities = User::where('role', 'rider')->whereNotNull('city')->where('city', '!=', '')->distinct()->pluck('city');

        $query = User::where('role', 'rider')
            ->withCount([
                'assignedDeliveries as active_deliveries_count' => function ($q) {
                    $q->whereIn('status', ['confirmed', 'preparing', 'delivering']);
                },
                'assignedDeliveries as completed_deliveries_count' => function ($q) {
                    $q->where('status', 'completed');
                }
            ])
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->when($city && $city !== 'all', function ($q) use ($city) {
                $q->where('city', $city);
            })
            ->when($status === 'active', function ($q) {
                $q->has('assignedDeliveries', '>=', 1, 'and', function ($delQuery) {
                    $delQuery->whereIn('status', ['confirmed', 'preparing', 'delivering']);
                });
            })
            ->when($status === 'idle', function ($q) {
                $q->whereDoesntHave('assignedDeliveries', function ($delQuery) {
                    $delQuery->whereIn('status', ['confirmed', 'preparing', 'delivering']);
                });
            });

        match ($sortBy) {
            'oldest' => $query->oldest(),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'completed_desc' => $query->orderByDesc('completed_deliveries_count'),
            'active_desc' => $query->orderByDesc('active_deliveries_count'),
            default => $query->latest(),
        };

        $riders = $query->paginate(10)->withQueryString();

        return view('admin.riders.index', compact(
            'riders',
            'cities',
            'search',
            'city',
            'status',
            'sortBy'
        ));
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

        $returnUrl = $request->input('return_url') ?: url()->previous(route('admin.riders.index'));
        return redirect()->to($returnUrl)
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

        $returnUrl = $request->input('return_url') ?: url()->previous(route('admin.riders.index'));
        return redirect()->to($returnUrl)
            ->with('success', "Rider '{$rider->name}' updated successfully! 🛵");
    }

    /**
     * Remove the specified rider from storage.
     */
    public function destroy(Request $request, User $rider)
    {
        // Unassign orders assigned to this rider so they aren't orphaned with invalid foreign key
        Order::where('rider_id', $rider->id)->update(['rider_id' => null]);

        $riderName = $rider->name;
        $rider->delete();

        $returnUrl = $request->input('return_url') ?: url()->previous(route('admin.riders.index'));
        return redirect()->to($returnUrl)
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

        $order->loadMissing('rider');
        $rider = $order->rider;
        $riderName = $rider ? $rider->name : 'Unassigned';

        if ($rider && in_array($order->status, ['confirmed', 'preparing', 'delivering'])) {
            \App\Services\PayslipService::sendRiderPayslip($order, $rider);
        }

        return back()->with('success', "Order #{$order->order_number} assigned to {$riderName}! FoodOrder delivery slip emailed to rider. 📦🛵");
    }
}

