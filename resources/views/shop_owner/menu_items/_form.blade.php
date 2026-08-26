{{-- Shared form partial for create/edit menu item modals in shop owner panel --}}
@php $isEdit = ($item === 'edit'); @endphp

<div>
    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Category <span class="text-red-500">*</span></label>
    <select name="category_id" id="{{ $isEdit ? 'edit_item_category' : 'create_item_category' }}"
            class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-500/30 focus:border-orange-400 outline-none transition-all" required>
        <option value="">— Select Category —</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
        @endforeach
    </select>
</div>

<div>
    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Item Name <span class="text-red-500">*</span></label>
    <input type="text" name="name" id="{{ $isEdit ? 'edit_item_name' : 'create_item_name' }}"
           placeholder="e.g. Pepperoni Pizza"
           class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-500/30 focus:border-orange-400 outline-none transition-all" required>
</div>

<div>
    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Description</label>
    <textarea name="description" id="{{ $isEdit ? 'edit_item_description' : 'create_item_description' }}"
              rows="2" placeholder="Brief description..."
              class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-500/30 focus:border-orange-400 outline-none transition-all resize-none"></textarea>
</div>

<div class="grid grid-cols-3 gap-3">
    <div>
        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Price (MMK) <span class="text-red-500">*</span></label>
        <input type="number" name="price" id="{{ $isEdit ? 'edit_item_price' : 'create_item_price' }}"
               min="0" step="100" placeholder="5000"
               class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-500/30 focus:border-orange-400 outline-none transition-all" required>
    </div>
    <div>
        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Stock <span class="text-red-500">*</span></label>
        <input type="number" name="stock" id="{{ $isEdit ? 'edit_item_stock' : 'create_item_stock' }}"
               min="0" placeholder="50"
               class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-500/30 focus:border-orange-400 outline-none transition-all" required>
    </div>
    <div>
        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Min Stock</label>
        <input type="number" name="min_stock_level" id="{{ $isEdit ? 'edit_item_min_stock' : 'create_item_min_stock' }}"
               min="0" placeholder="10"
               class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-500/30 focus:border-orange-400 outline-none transition-all">
    </div>
</div>

<div class="flex items-center gap-3">
    <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="is_available" id="{{ $isEdit ? 'edit_item_available' : 'create_item_available' }}"
               value="1" checked
               class="w-4 h-4 rounded border-slate-300 text-orange-500 focus:ring-orange-500/30">
        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Available for ordering</span>
    </label>
</div>

<div>
    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Image</label>
    <input type="file" name="image" accept="image/*"
           class="w-full text-xs text-slate-600 dark:text-slate-400 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100 dark:file:bg-orange-950/30 dark:file:text-orange-400 cursor-pointer">
</div>
