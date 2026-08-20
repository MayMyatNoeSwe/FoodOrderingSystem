<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
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
        View::composer(['admin.*', 'components.admin-sidebar'], function ($view) {
            $view->with([
                'navCategoryCount'  => Category::count(),
                'navMenuItemCount'  => MenuItem::count(),
                'navOrderCount'     => Order::count(),
                'navOrderItemCount' => OrderItem::count(),
                'navRiderCount'     => User::where('role', 'rider')->count(),
                'navCustomerCount'  => User::where('role', 'user')->count(),
                'navUserCount'      => User::count(),
            ]);
        });
    }
}
