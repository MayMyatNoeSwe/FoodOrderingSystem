<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.admin-sidebar', function ($view) {
            $counts = Cache::remember('admin_nav_counts', 15, function () {
                return [
                    'navCategoryCount'            => Category::count(),
                    'navMenuItemCount'            => MenuItem::count(),
                    'navInventoryCount'           => MenuItem::count(),
                    'navInventoryOutOfStockCount' => MenuItem::where('is_available', false)->orWhere('stock', '<=', 0)->count(),
                    'navOrderCount'               => Order::count(),
                    'navOrderItemCount'           => OrderItem::count(),
                    'navRiderCount'               => User::where('role', 'rider')->count(),
                    'navCustomerCount'            => User::where('role', 'user')->count(),
                    'navUserCount'                => User::count(),
                    'navPendingComplaintCount'    => Complaint::where('status', 'pending')->count(),
                ];
            });

            $view->with($counts);
        });
    }
}
