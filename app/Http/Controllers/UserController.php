<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of registered users in admin panel.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $role = $request->query('role');

        // User Statistics in single aggregate query
        $stats = User::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count,
            SUM(CASE WHEN role = 'rider' THEN 1 ELSE 0 END) as rider_count,
            SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as customer_count,
            SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as new_this_month
        ", [now()->startOfMonth()])->first();

        $totalUsersCount = (int)($stats->total ?? 0);
        $adminCount = (int)($stats->admin_count ?? 0);
        $riderCount = (int)($stats->rider_count ?? 0);
        $customerCount = (int)($stats->customer_count ?? 0);
        $newThisMonthCount = (int)($stats->new_this_month ?? 0);

        $users = User::withCount('orders')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($role, function ($query, $role) {
                return $query->where('role', $role);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact(
            'users',
            'search',
            'role',
            'totalUsersCount',
            'adminCount',
            'riderCount',
            'customerCount',
            'newThisMonthCount'
        ));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:user,admin',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        $returnUrl = $request->input('return_url') ?: url()->previous(route('admin.users.index'));
        return redirect()->to($returnUrl)->with('success', "User '{$validated['name']}' created successfully!");
    }

    /**
     * Update the specified user role or details.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role'     => 'required|string|in:user,admin',
            'name'     => 'nullable|string|max:255',
            'email'    => ['nullable', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
        ]);

        $updateData = ['role' => $validated['role']];
        if (!empty($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }
        if (!empty($validated['email'])) {
            $updateData['email'] = $validated['email'];
        }
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        $returnUrl = $request->input('return_url') ?: url()->previous(route('admin.users.index'));
        return redirect()->to($returnUrl)->with('success', "Role for '{$user->name}' updated to " . ($validated['role'] === 'admin' ? 'System Administrator' : 'Customer') . " successfully!");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, User $user)
    {
        $returnUrl = $request->input('return_url') ?: url()->previous(route('admin.users.index'));
        if ($user->id === Auth::id()) {
            return redirect()->to($returnUrl)->with('error', 'You cannot delete your own logged-in admin account!');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->to($returnUrl)->with('success', "User '{$userName}' deleted successfully!");
    }
}
