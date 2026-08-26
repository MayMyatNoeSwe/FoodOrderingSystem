@extends('layouts.shop_owner')

@section('heading', 'No Shop Assigned')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[60vh] text-center">
    <div class="text-8xl mb-6">🏪</div>
    <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-3">No Shop Assigned Yet</h2>
    <p class="text-slate-500 dark:text-slate-400 max-w-sm text-sm">
        Your account doesn't have a shop assigned to it yet. Please contact the system administrator to create and assign a shop to your account.
    </p>
    <div class="mt-8 flex items-center gap-3">
        <a href="{{ route('home') }}"
           class="px-5 py-2.5 bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold rounded-xl text-sm hover:from-orange-600 hover:to-amber-600 transition-all shadow-md">
            Go to Storefront
        </a>
        <form method="POST" action="{{ route('logout') }}" onsubmit="localStorage.removeItem('foodorder_cart')">
            @csrf
            <button type="submit" class="px-5 py-2.5 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-all cursor-pointer">
                Sign Out
            </button>
        </form>
    </div>
</div>
@endsection
