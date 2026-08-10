<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center w-full px-5 py-3 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-semibold text-sm rounded-xl shadow-lg shadow-orange-500/25 focus:outline-none focus:ring-4 focus:ring-orange-500/20 transition-all duration-200 cursor-pointer']) }}>
    {{ $slot }}
</button>

