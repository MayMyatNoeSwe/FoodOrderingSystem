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
        $orderId = $request->query('order_id');
        $status = $request->query('status');

        $shopId = $request->query('shop_id');
        $sortBy = $request->query('sort_by', 'latest');

        $categories = Category::orderBy('name', 'asc')->get();
        $shops = \App\Models\Shop::orderBy('name')->get(['id', 'name']);

        // Metrics Summary
        $totalQuantitySold = OrderItem::sum('quantity') ?: 0;
        $totalItemsRevenue = OrderItem::sum('subtotal') ?: 0;
        $uniqueMenuItemsCount = OrderItem::distinct('menu_item_id')->count('menu_item_id');
        $avgItemPrice = $totalQuantitySold > 0 ? round($totalItemsRevenue / $totalQuantitySold) : 0;

        $topItemRow = OrderItem::select('menu_item_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('menu_item_id')
            ->orderByDesc('total_qty')
            ->with('menuItem')
            ->first();

        $topItemName = $topItemRow && $topItemRow->menuItem ? $topItemRow->menuItem->name : 'N/A';

        $query = OrderItem::with(['order.user', 'order.shop', 'menuItem.category', 'menuItem.shop'])
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
            ->when($shopId && $shopId !== 'all', function ($query) use ($shopId) {
                return $query->whereHas('order', function ($q) use ($shopId) {
                    $q->where('shop_id', $shopId);
                });
            })
            ->when($categoryId, function ($query, $categoryId) {
                return $query->whereHas('menuItem', function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            })
            ->when($orderId, function ($query, $orderId) {
                return $query->where('order_id', $orderId);
            })
            ->when($status, function ($query, $status) {
                return $query->whereHas('order', function ($q) use ($status) {
                    $q->where('status', $status);
                });
            });

        match ($sortBy) {
            'oldest'        => $query->oldest(),
            'subtotal_desc' => $query->orderByDesc('subtotal'),
            'subtotal_asc'  => $query->orderBy('subtotal', 'asc'),
            'qty_desc'      => $query->orderByDesc('quantity'),
            'qty_asc'       => $query->orderBy('quantity', 'asc'),
            default         => $query->latest(),
        };

        $orderItems = $query->paginate(15)->withQueryString();

        return view('admin.orderItems.index', compact(
            'orderItems',
            'categories',
            'shops',
            'search',
            'shopId',
            'categoryId',
            'orderId',
            'status',
            'sortBy',
            'totalQuantitySold',
            'totalItemsRevenue',
            'uniqueMenuItemsCount',
            'avgItemPrice',
            'topItemName'
        ));
    }

    /**
     * Remove the specified order item from storage.
     */
    public function destroy(Request $request, OrderItem $orderItem)
    {
        $itemName = $orderItem->menuItem ? $orderItem->menuItem->name : 'Item';
        OrderItem::destroy($orderItem->id);

        $returnUrl = $request->input('return_url') ?: url()->previous(route('admin.orderItems.index'));
        return redirect()->to($returnUrl)->with('success', "Order item '{$itemName}' deleted successfully!");
    }
}
