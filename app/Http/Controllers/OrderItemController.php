<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderItemController extends Controller
{
    /**
     * Display a listing of order items in admin panel.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $status = $request->query('status');

        $categories = Category::orderBy('name', 'asc')->get();

        // Metrics Summary
        $totalQuantitySold = OrderItem::sum('quantity');
        $totalItemsRevenue = OrderItem::sum('subtotal');

        $topItemRow = OrderItem::select('menu_item_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('menu_item_id')
            ->orderByDesc('total_qty')
            ->with('menuItem')
            ->first();

        $topItemName = $topItemRow && $topItemRow->menuItem ? $topItemRow->menuItem->name : 'N/A';

        $orderItems = OrderItem::with(['order.user', 'menuItem.category'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('menuItem', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('order', function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($u) use ($search) {
                          $u->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->when($categoryId, function ($query, $categoryId) {
                return $query->whereHas('menuItem', function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            })
            ->when($status, function ($query, $status) {
                return $query->whereHas('order', function ($q) use ($status) {
                    $q->where('status', $status);
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orderItems.index', compact(
            'orderItems',
            'categories',
            'search',
            'categoryId',
            'status',
            'totalQuantitySold',
            'totalItemsRevenue',
            'topItemName'
        ));
    }

    /**
     * Remove the specified order item from storage.
     */
    public function destroy(OrderItem $orderItem)
    {
        $itemName = $orderItem->menuItem ? $orderItem->menuItem->name : 'Item';
        OrderItem::destroy($orderItem->id);

        return redirect()->route('admin.orderItems.index')->with('success', "Order item '{$itemName}' deleted successfully!");
    }
}

