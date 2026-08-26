<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\Shop;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $shopId = $request->query('shop_id');
        $sortBy = $request->query('sort_by', 'latest');

        $query = Category::with('shop')->withCount('menuItems')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                             ->orWhere('slug', 'like', "%{$search}%");
            })
            ->when($shopId && $shopId !== 'all', function ($query) use ($shopId) {
                return $query->where('shop_id', $shopId);
            });

        match ($sortBy) {
            'oldest'           => $query->oldest(),
            'name_asc'         => $query->orderBy('name', 'asc'),
            'name_desc'        => $query->orderBy('name', 'desc'),
            'items_count_desc' => $query->orderByDesc('menu_items_count'),
            default            => $query->latest(),
        };

        $categories = $query->paginate(10)->withQueryString();
        $shops = Shop::orderBy('name')->get(['id', 'name']);

        return view('admin.categories.index', compact('categories', 'shops', 'search', 'shopId', 'sortBy'));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        $returnUrl = $request->input('return_url') ?: url()->previous(route('admin.categories.index'));
        return redirect()->to($returnUrl)->with('success', 'Category created successfully!');
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        $returnUrl = $request->input('return_url') ?: url()->previous(route('admin.categories.index'));
        return redirect()->to($returnUrl)->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Request $request, Category $category)
    {
        $category->delete();

        $returnUrl = $request->input('return_url') ?: url()->previous(route('admin.categories.index'));
        return redirect()->to($returnUrl)->with('success', 'Category deleted successfully!');
    }
}
