<?php

namespace App\Http\Controllers\ShopOwner;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ShopCategoryController extends Controller
{
    private function getOwnerShop()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->ownedShop;
    }

    /**
     * Display the shop's categories.
     */
    public function index(Request $request): View
    {
        $shop = $this->getOwnerShop();
        abort_if(!$shop, 403, 'You do not have a shop assigned.');

        $search = $request->query('search');
        $sortBy = $request->query('sort_by', 'name_asc');

        $query = Category::where('shop_id', $shop->id)
            ->withCount('menuItems')
            ->when($search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%");
            });

        match ($sortBy) {
            'name_desc'        => $query->orderBy('name', 'desc'),
            'items_count_desc' => $query->orderByDesc('menu_items_count'),
            'latest'           => $query->latest(),
            'oldest'           => $query->oldest(),
            default            => $query->orderBy('name', 'asc'),
        };

        $categories = $query->paginate(10)->withQueryString();

        return view('shop_owner.categories.index', compact('shop', 'categories', 'search', 'sortBy'));
    }

    /**
     * Store a new category for the owner's shop.
     */
    public function store(Request $request): RedirectResponse
    {
        $shop = $this->getOwnerShop();
        abort_if(!$shop, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $baseSlug = Str::slug($validated['name']);
        $slug     = $baseSlug;
        $counter  = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        Category::create([
            'shop_id' => $shop->id,
            'name'    => $validated['name'],
            'slug'    => $slug,
        ]);

        return redirect()->route('shop_owner.categories.index')
            ->with('success', "Category '{$validated['name']}' created! 📂");
    }

    /**
     * Update a category.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $shop = $this->getOwnerShop();
        abort_if(!$shop || $category->shop_id !== $shop->id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category->update(['name' => $validated['name']]);

        return redirect()->route('shop_owner.categories.index')
            ->with('success', "Category updated! ✅");
    }

    /**
     * Delete a category.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $shop = $this->getOwnerShop();
        abort_if(!$shop || $category->shop_id !== $shop->id, 403);

        $name = $category->name;
        $category->delete();

        return redirect()->route('shop_owner.categories.index')
            ->with('success', "Category '{$name}' deleted. 🗑️");
    }
}
