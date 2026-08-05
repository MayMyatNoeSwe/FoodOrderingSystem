<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;


// ၁။ မူလစာမျက်နှာ (Home / Menu List)
Route::get('/', [MenuItemController::class, 'index'])->name('menu.index');

// ၂။ Category အလိုက် မီနူးများ ခွဲခြားကြည့်ရန်
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

// ၃။ Menu Item တစ်ခုချင်းစီ၏ Detail ကြည့်ရန်
Route::get('/menu-items/{menuItem}', [MenuItemController::class, 'show'])->name('menu_items.show');

// ၄။ Order တင်ရန် (Checkout & Store)
Route::get('/checkout', [OrderController::class, 'create'])->name('orders.create');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

// ၅။ မှာယူပြီးသော Order အခြေအနေနှင့် အသေးစိတ်ကြည့်ရန်
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

// ၆။ (Optional) Order ထဲမှ Item တစ်ခုချင်းစီကို ပြင်ဆင်/ဖျက်ရန်
Route::delete('/order-items/{orderItem}', [OrderItemController::class, 'destroy'])->name('order_items.destroy');