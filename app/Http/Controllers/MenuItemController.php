<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuItemController extends Controller
{
    /**
     * Display a listing of the menu items.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');

        $categories = Category::orderBy('name', 'asc')->get();

        $menuItems = MenuItem::with('category')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                             ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($categoryId, function ($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.menuItems.index', compact('menuItems', 'categories', 'search', 'categoryId'));
    }

    /**
     * Store a newly created menu item in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_available' => 'nullable|boolean',
        ]);

        $imagePath = $validated['image'] ?? null;
        if ($request->hasFile('image_file')) {
            $imagePath = $request->file('image_file')->store('menu_items', 'public');
        }

        MenuItem::create([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'price' => $validated['price'],
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
            'is_available' => $request->has('is_available') ? $request->boolean('is_available') : true,
        ]);

        return redirect()->route('admin.menuItems.index')->with('success', 'Menu item created successfully!');
    }

    /**
     * Update the specified menu item in storage.
     */
    public function update(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_available' => 'nullable|boolean',
        ]);

        $imagePath = $menuItem->image;
        if ($request->hasFile('image_file')) {
            if ($menuItem->image && !str_starts_with($menuItem->image, '/images/') && !str_starts_with($menuItem->image, 'images/') && Storage::disk('public')->exists($menuItem->image)) {
                Storage::disk('public')->delete($menuItem->image);
            }
            $imagePath = $request->file('image_file')->store('menu_items', 'public');
        } elseif ($request->filled('image')) {
            $imagePath = trim($request->input('image'));
        }

        $menuItem->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'price' => $validated['price'],
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
            'is_available' => $request->has('is_available') ? $request->boolean('is_available') : false,
        ]);

        return redirect()->route('admin.menuItems.index')->with('success', 'Menu item updated successfully!');
    }

    /**
     * Remove the specified menu item from storage.
     */
    public function destroy(MenuItem $menuItem)
    {
        if ($menuItem->image && Storage::disk('public')->exists($menuItem->image)) {
            Storage::disk('public')->delete($menuItem->image);
        }

        MenuItem::destroy($menuItem->id);

        return redirect()->route('admin.menuItems.index')->with('success', 'Menu item deleted successfully!');
    }
}
