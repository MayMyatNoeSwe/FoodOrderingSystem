<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ShopController extends Controller
{
    /**
     * Display a listing of all shops.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $sortBy = $request->query('sort_by', 'latest');

        $shopsQuery = Shop::with('owner')
            ->withCount(['menuItems', 'orders'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($status && $status !== 'all', function ($query) use ($status) {
                $query->where('status', $status);
            });

        match ($sortBy) {
            'oldest'       => $shopsQuery->oldest(),
            'name_asc'     => $shopsQuery->orderBy('name', 'asc'),
            'name_desc'    => $shopsQuery->orderBy('name', 'desc'),
            'items_count'  => $shopsQuery->orderByDesc('menu_items_count'),
            'orders_count' => $shopsQuery->orderByDesc('orders_count'),
            default        => $shopsQuery->latest(),
        };

        $shops = $shopsQuery->paginate(9)->withQueryString();

        $shopOwners = User::where('role', 'shop_owner')->orderBy('name')->get();
        $availableOwners = User::where(function ($q) {
            $q->where('role', 'shop_owner')
              ->orWhere('role', 'user');
        })->whereDoesntHave('ownedShop')->orderBy('name')->get();

        return view('admin.shops.index', compact('shops', 'shopOwners', 'availableOwners', 'search', 'status', 'sortBy'));
    }

    /**
     * Store a newly created shop.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'address'     => 'required|string|max:500',
            'phone'       => 'nullable|string|max:30',
            'email'       => 'nullable|email|max:255',
            'status'      => 'required|in:active,inactive,pending',
            'owner_id'    => 'nullable|exists:users,id',
            'logo'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(6);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $fileName = time() . '_logo_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/shops'), $fileName);
            $validated['logo'] = 'uploads/shops/' . $fileName;
        }

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $fileName = time() . '_cover_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/shops'), $fileName);
            $validated['cover_image'] = 'uploads/shops/' . $fileName;
        }

        $shop = Shop::create($validated);

        // If owner assigned, update their role to shop_owner
        if (!empty($validated['owner_id'])) {
            User::where('id', $validated['owner_id'])->update(['role' => 'shop_owner']);
        }

        return redirect()->route('admin.shops.index')
            ->with('success', "Shop '{$shop->name}' created successfully! 🏪");
    }

    /**
     * Update the specified shop.
     */
    public function update(Request $request, Shop $shop): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'address'     => 'required|string|max:500',
            'phone'       => 'nullable|string|max:30',
            'email'       => 'nullable|email|max:255',
            'status'      => 'required|in:active,inactive,pending',
            'owner_id'    => 'nullable|exists:users,id',
            'logo'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $fileName = time() . '_logo_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/shops'), $fileName);
            $validated['logo'] = 'uploads/shops/' . $fileName;
        } else {
            unset($validated['logo']); // Don't overwrite existing logo if none uploaded
        }

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $fileName = time() . '_cover_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/shops'), $fileName);
            $validated['cover_image'] = 'uploads/shops/' . $fileName;
        } else {
            unset($validated['cover_image']);
        }

        $oldOwnerId = $shop->owner_id;
        $shop->update($validated);

        // Update new owner's role to shop_owner if changed
        if (!empty($validated['owner_id']) && $validated['owner_id'] != $oldOwnerId) {
            User::where('id', $validated['owner_id'])->update(['role' => 'shop_owner']);
            // Demote previous owner back to user if they own no other shop
            if ($oldOwnerId) {
                $prevOwner = User::find($oldOwnerId);
                if ($prevOwner && !$prevOwner->ownedShop()->exists()) {
                    $prevOwner->update(['role' => 'user']);
                }
            }
        }

        return redirect()->route('admin.shops.index')
            ->with('success', "Shop '{$shop->name}' updated successfully! ✅");
    }

    /**
     * Delete the specified shop.
     */
    public function destroy(Shop $shop): RedirectResponse
    {
        $shopName = $shop->name;
        $ownerId  = $shop->owner_id;

        $shop->delete();

        // Demote owner back to regular user if they have no other shops
        if ($ownerId) {
            $owner = User::find($ownerId);
            if ($owner && !$owner->ownedShop()->exists()) {
                $owner->update(['role' => 'user']);
            }
        }

        return redirect()->route('admin.shops.index')
            ->with('success', "Shop '{$shopName}' deleted. 🗑️");
    }

    /**
     * Toggle the shop status (active ↔ inactive).
     */
    public function toggleStatus(Shop $shop): RedirectResponse
    {
        $newStatus = $shop->status === 'active' ? 'inactive' : 'active';
        $shop->update(['status' => $newStatus]);

        return redirect()->route('admin.shops.index')
            ->with('success', "Shop '{$shop->name}' is now {$newStatus}.");
    }
}
