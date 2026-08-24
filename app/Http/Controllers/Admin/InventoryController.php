<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display the inventory and stock control hub.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $stockStatus = $request->query('stock_status', 'all');

        $categories = Category::select('id', 'name')->orderBy('name', 'asc')->get();

        // Overall metric stats in single aggregate query
        $stats = MenuItem::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN is_available = 1 AND stock > 0 THEN 1 ELSE 0 END) as in_stock,
            SUM(CASE WHEN stock > 0 AND stock <= 10 THEN 1 ELSE 0 END) as low_stock,
            SUM(CASE WHEN is_available = 0 OR stock <= 0 THEN 1 ELSE 0 END) as out_of_stock
        ")->first();

        $totalItemsCount = (int)($stats->total ?? 0);
        $inStockCount = (int)($stats->in_stock ?? 0);
        $lowStockCount = (int)($stats->low_stock ?? 0);
        $outOfStockCount = (int)($stats->out_of_stock ?? 0);

        // Query with filters
        $itemsQuery = MenuItem::with('category')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhereHas('category', function ($catQuery) use ($search) {
                          $catQuery->where('name', 'like', '%' . $search . '%');
                      });
                });
            })
            ->when($categoryId, function ($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->when($stockStatus && $stockStatus !== 'all', function ($query) use ($stockStatus) {
                if ($stockStatus === 'available') {
                    return $query->where('is_available', true)->where('stock', '>', 0);
                } elseif ($stockStatus === 'low_stock') {
                    return $query->where('stock', '>', 0)->where('stock', '<=', 10);
                } elseif ($stockStatus === 'out_of_stock') {
                    return $query->where('is_available', false)->orWhere('stock', '<=', 0);
                }
            });

        $menuItems = $itemsQuery->orderBy('name', 'asc')->get();

        return view('admin.inventory.index', compact(
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
     * Instant 1-Click Toggle for stock availability.
     */
    public function toggleStock(Request $request, MenuItem $menuItem)
    {
        $newStatus = !$menuItem->is_available;
        $updates = ['is_available' => $newStatus];

        // If activating and stock is 0, give it default minimum stock of 10 so it's orderable
        if ($newStatus && $menuItem->stock <= 0) {
            $updates['stock'] = 10;
        }

        $menuItem->update($updates);

        $statusText = $menuItem->is_available ? 'Available (In-Stock) ✅' : 'Out of Stock (Disabled) 🚫';
        $message = "Dish '{$menuItem->name}' is now marked as {$statusText}";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_available' => $menuItem->is_available,
                'stock' => $menuItem->stock,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Update stock level for a specific menu item.
     */
    public function updateStock(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'stock' => 'required|integer|min:0|max:99999',
            'is_available' => 'nullable|boolean',
        ]);

        $stock = (int) $validated['stock'];
        $isAvailable = $request->has('is_available') ? $request->boolean('is_available') : ($stock > 0);

        $menuItem->update([
            'stock' => $stock,
            'is_available' => $isAvailable,
        ]);

        $message = "Stock level for '{$menuItem->name}' updated to {$stock} units.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'stock' => $menuItem->stock,
                'is_available' => $menuItem->is_available,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Bulk restock low-stock and out-of-stock items.
     */
    public function bulkRestock(Request $request)
    {
        $validated = $request->validate([
            'target' => 'required|in:low_stock,out_of_stock,all',
            'amount' => 'required|integer|min:1|max:1000',
        ]);

        $amount = (int) $validated['amount'];
        $target = $validated['target'];

        $query = MenuItem::query();

        if ($target === 'low_stock') {
            $query->where('stock', '<=', 10);
        } elseif ($target === 'out_of_stock') {
            $query->where('is_available', false)->orWhere('stock', '<=', 0);
        }

        $affectedCount = $query->count();

        $query->update([
            'stock' => $amount,
            'is_available' => true,
        ]);

        return back()->with('success', "Successfully restocked {$affectedCount} items to {$amount} units & set to Available! 🚀");
    }
}
