<?php

namespace App\Http\Controllers\ShopOwner;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ShopMenuItemController extends Controller
{
    private function getOwnerShop()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->ownedShop;
    }

    /**
     * Display the shop's menu items.
     */
    public function index(Request $request): View
    {
        $shop = $this->getOwnerShop();
        abort_if(!$shop, 403, 'You do not have a shop assigned.');

        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $stockStatus = $request->query('stock_status', 'all');
        $sortBy = $request->query('sort_by', 'latest');

        $query = MenuItem::where('shop_id', $shop->id)
            ->with('category')
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, function ($q, $categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->when($stockStatus && $stockStatus !== 'all', function ($q) use ($stockStatus) {
                if ($stockStatus === 'available') {
                    $q->where('is_available', true)->where('stock', '>', 0);
                } elseif ($stockStatus === 'low_stock') {
                    $q->where('stock', '>', 0)->where('stock', '<=', 10);
                } elseif ($stockStatus === 'out_of_stock') {
                    $q->where('is_available', false)->orWhere('stock', '<=', 0);
                }
            });

        match ($sortBy) {
            'oldest'     => $query->oldest(),
            'name_asc'   => $query->orderBy('name', 'asc'),
            'name_desc'  => $query->orderBy('name', 'desc'),
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderByDesc('price'),
            'stock_desc' => $query->orderByDesc('stock'),
            'stock_asc'  => $query->orderBy('stock', 'asc'),
            default      => $query->latest(),
        };

        $menuItems = $query->paginate(10)->withQueryString();
        $categories = $shop->categories()->orderBy('name')->get();

        return view('shop_owner.menu_items.index', compact(
            'shop',
            'menuItems',
            'categories',
            'search',
            'categoryId',
            'stockStatus',
            'sortBy'
        ));
    }

    /**
     * Store a new menu item for the owner's shop.
     */
    public function store(Request $request): RedirectResponse
    {
        $shop = $this->getOwnerShop();
        abort_if(!$shop, 403);

        $validated = $request->validate([
            'category_id'    => 'required|exists:categories,id',
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string|max:1000',
            'price'          => 'required|numeric|min:0',
            'stock'          => 'required|integer|min:0',
            'min_stock_level'=> 'nullable|integer|min:0',
            'is_available'   => 'nullable|boolean',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        // Ensure category belongs to this shop
        abort_if(
            !$shop->categories()->where('id', $validated['category_id'])->exists(),
            403,
            'Category does not belong to your shop.'
        );

        $validated['shop_id']      = $shop->id;
        $validated['is_available'] = $request->boolean('is_available', true);

        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/menu_items'), $fileName);
            $validated['image'] = 'uploads/menu_items/' . $fileName;
        }

        MenuItem::create($validated);

        return redirect()->route('shop_owner.menu-items.index')
            ->with('success', "Menu item '{$validated['name']}' added! 🍽️");
    }

    /**
     * Update an existing menu item.
     */
    public function update(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $shop = $this->getOwnerShop();
        abort_if(!$shop || $menuItem->shop_id !== $shop->id, 403);

        $validated = $request->validate([
            'category_id'    => 'required|exists:categories,id',
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string|max:1000',
            'price'          => 'required|numeric|min:0',
            'stock'          => 'required|integer|min:0',
            'min_stock_level'=> 'nullable|integer|min:0',
            'is_available'   => 'nullable|boolean',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $validated['is_available'] = $request->boolean('is_available', true);

        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/menu_items'), $fileName);
            $validated['image'] = 'uploads/menu_items/' . $fileName;
        } else {
            unset($validated['image']);
        }

        $menuItem->update($validated);

        return redirect()->route('shop_owner.menu-items.index')
            ->with('success', "'{$menuItem->name}' updated! ✅");
    }

    /**
     * Delete a menu item.
     */
    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        $shop = $this->getOwnerShop();
        abort_if(!$shop || $menuItem->shop_id !== $shop->id, 403);

        $name = $menuItem->name;
        $menuItem->delete();

        return redirect()->route('shop_owner.menu-items.index')
            ->with('success', "'{$name}' deleted. 🗑️");
    }
}
