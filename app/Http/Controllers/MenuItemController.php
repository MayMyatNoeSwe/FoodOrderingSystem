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
        $stockStatus = $request->query('stock_status');

        $categories = Category::select('id', 'name')->orderBy('name', 'asc')->get();

        // Stock stats using single aggregated query
        $stats = MenuItem::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN stock > 10 THEN 1 ELSE 0 END) as in_stock,
            SUM(CASE WHEN stock > 0 AND stock <= 10 THEN 1 ELSE 0 END) as low_stock,
            SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as out_of_stock
        ")->first();

        $totalItemsCount = (int)($stats->total ?? 0);
        $inStockCount = (int)($stats->in_stock ?? 0);
        $lowStockCount = (int)($stats->low_stock ?? 0);
        $outOfStockCount = (int)($stats->out_of_stock ?? 0);

        $menuItems = MenuItem::with('category')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%');
            })
            ->when($categoryId, function ($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->when($stockStatus, function ($query, $stockStatus) {
                if ($stockStatus === 'in_stock') {
                    return $query->where('stock', '>', 10);
                } elseif ($stockStatus === 'low_stock') {
                    return $query->where('stock', '>', 0)->where('stock', '<=', 10);
                } elseif ($stockStatus === 'out_of_stock') {
                    return $query->where('stock', 0);
                }
                return $query;
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.menuItems.index', compact(
            'menuItems', 
            'categories', 
            'search', 
            'categoryId', 
            'stockStatus',
            'totalItemsCount',
            'inStockCount',
            'lowStockCount',
            'outOfStockCount'
        ));
    }

    /**
     * Store a newly created menu item in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'category_id'      => 'required|exists:categories,id',
            'price'            => 'required|numeric|min:0',
            'min_stock_level'  => 'nullable|integer|min:0',
            'stock'            => 'nullable|integer|min:0',
            'description'      => 'nullable|string',
            'image'            => 'nullable|string|max:1000',
            'image_file'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'image_files'      => 'nullable|array',
            'image_files.*'    => 'image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'is_available'     => 'nullable|boolean',
        ]);

        $imagesList = [];

        // 1. Process URL text (support multiple URLs split by comma or newline)
        if ($request->filled('image')) {
            $rawUrls = preg_split('/[\r\n,]+/', $request->input('image'));
            foreach ($rawUrls as $url) {
                $url = trim($url);
                if (!empty($url)) {
                    $imagesList[] = $url;
                }
            }
        }

        // 2. Process single uploaded file
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('menu_items', 'public');
            $imagesList[] = $path;
        }

        // 3. Process multiple uploaded files
        if ($request->hasFile('image_files')) {
            foreach ($request->file('image_files') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('menu_items', 'public');
                    $imagesList[] = $path;
                }
            }
        }

        $imagesList = array_values(array_unique(array_filter($imagesList)));
        $primaryImage = $imagesList[0] ?? null;

        MenuItem::create([
            'name'            => $validated['name'],
            'category_id'     => $validated['category_id'],
            'price'           => $validated['price'],
            'stock'           => $request->filled('stock') ? (int)$request->input('stock') : 999,
            'min_stock_level' => $request->filled('min_stock_level') ? (int)$request->input('min_stock_level') : 10,
            'description'     => $validated['description'] ?? null,
            'image'           => $primaryImage,
            'images'          => $imagesList,
            'is_available'    => $request->has('is_available') ? $request->boolean('is_available') : true,
        ]);

        $returnUrl = $request->input('return_url') ?: url()->previous(route('admin.menuItems.index'));
        return redirect()->to($returnUrl)->with('success', 'Item created successfully with photos & min stock level!');
    }

    /**
     * Update the specified menu item in storage.
     */
    public function update(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'category_id'      => 'required|exists:categories,id',
            'price'            => 'required|numeric|min:0',
            'min_stock_level'  => 'nullable|integer|min:0',
            'stock'            => 'nullable|integer|min:0',
            'description'      => 'nullable|string',
            'image'            => 'nullable|string|max:1000',
            'existing_images'  => 'nullable|array',
            'image_file'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'image_files'      => 'nullable|array',
            'image_files.*'    => 'image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'is_available'     => 'nullable|boolean',
        ]);

        $imagesList = [];

        // 1. Keep retained existing images
        if ($request->has('existing_images') && is_array($request->input('existing_images'))) {
            foreach ($request->input('existing_images') as $ex) {
                if (!empty($ex)) {
                    $imagesList[] = trim($ex);
                }
            }
        } elseif ($request->filled('image')) {
            $rawUrls = preg_split('/[\r\n,]+/', $request->input('image'));
            foreach ($rawUrls as $url) {
                $url = trim($url);
                if (!empty($url)) {
                    $imagesList[] = $url;
                }
            }
        } elseif ($menuItem->images && is_array($menuItem->images)) {
            $imagesList = $menuItem->images;
        } elseif ($menuItem->image) {
            $imagesList = [$menuItem->image];
        }

        // 2. Process single uploaded file
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('menu_items', 'public');
            $imagesList[] = $path;
        }

        // 3. Process multiple uploaded files
        if ($request->hasFile('image_files')) {
            foreach ($request->file('image_files') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('menu_items', 'public');
                    $imagesList[] = $path;
                }
            }
        }

        $imagesList = array_values(array_unique(array_filter($imagesList)));
        $primaryImage = $imagesList[0] ?? $menuItem->image;

        $menuItem->update([
            'name'            => $validated['name'],
            'category_id'     => $validated['category_id'],
            'price'           => $validated['price'],
            'stock'           => $request->filled('stock') ? (int)$request->input('stock') : ($menuItem->stock ?? 999),
            'min_stock_level' => $request->filled('min_stock_level') ? (int)$request->input('min_stock_level') : ($menuItem->min_stock_level ?? 10),
            'description'     => $validated['description'] ?? null,
            'image'           => $primaryImage,
            'images'          => $imagesList,
            'is_available'    => $request->has('is_available') ? $request->boolean('is_available') : false,
        ]);

        $returnUrl = $request->input('return_url') ?: url()->previous(route('admin.menuItems.index'));
        return redirect()->to($returnUrl)->with('success', 'Item updated successfully!');
    }

    /**
     * Remove the specified menu item from storage.
     */
    public function destroy(Request $request, MenuItem $menuItem)
    {
        if ($menuItem->image && Storage::disk('public')->exists($menuItem->image)) {
            Storage::disk('public')->delete($menuItem->image);
        }

        if ($menuItem->images && is_array($menuItem->images)) {
            foreach ($menuItem->images as $img) {
                if (Storage::disk('public')->exists($img)) {
                    Storage::disk('public')->delete($img);
                }
            }
        }

        MenuItem::destroy($menuItem->id);

        $returnUrl = $request->input('return_url') ?: url()->previous(route('admin.menuItems.index'));
        return redirect()->to($returnUrl)->with('success', 'Item deleted successfully!');
    }
}
