{{-- Shared form fields for Create / Edit shop modals --}}
@php $isEdit = ($shop === 'edit'); @endphp

<div>
    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Shop Name <span class="text-red-500">*</span></label>
    <input type="text" name="name" id="{{ $isEdit ? 'edit_shop_name' : 'create_shop_name' }}"
           placeholder="e.g. Pizza Palace"
           class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-500/30 focus:border-orange-400 outline-none transition-all"
           required>
</div>

<div>
    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Description</label>
    <textarea name="description" id="{{ $isEdit ? 'edit_shop_description' : 'create_shop_description' }}"
              rows="2"
              placeholder="Brief description of the shop..."
              class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-500/30 focus:border-orange-400 outline-none transition-all resize-none"></textarea>
</div>

<div>
    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Address <span class="text-red-500">*</span></label>
    <input type="text" name="address" id="{{ $isEdit ? 'edit_shop_address' : 'create_shop_address' }}"
           placeholder="e.g. No. 45, Pyay Road, Kamayut, Yangon"
           class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-500/30 focus:border-orange-400 outline-none transition-all"
           required>
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Phone</label>
        <input type="text" name="phone" id="{{ $isEdit ? 'edit_shop_phone' : 'create_shop_phone' }}"
               placeholder="09xxxxxxxx"
               class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-500/30 focus:border-orange-400 outline-none transition-all">
    </div>
    <div>
        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email</label>
        <input type="email" name="email" id="{{ $isEdit ? 'edit_shop_email' : 'create_shop_email' }}"
               placeholder="shop@example.com"
               class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-500/30 focus:border-orange-400 outline-none transition-all">
    </div>
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Status <span class="text-red-500">*</span></label>
        <select name="status" id="{{ $isEdit ? 'edit_shop_status' : 'create_shop_status' }}"
                class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-500/30 focus:border-orange-400 outline-none transition-all">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="pending">Pending</option>
        </select>
    </div>
    <div>
        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Assign Owner</label>
        <select name="owner_id" id="{{ $isEdit ? 'edit_shop_owner' : 'create_shop_owner' }}"
                class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-500/30 focus:border-orange-400 outline-none transition-all">
            <option value="">— No owner —</option>
            @foreach($availableOwners as $owner)
                <option value="{{ $owner->id }}">{{ $owner->name }} ({{ $owner->email }})</option>
            @endforeach
        </select>
    </div>
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Logo</label>
        <input type="file" name="logo" accept="image/*"
               class="w-full text-xs text-slate-600 dark:text-slate-400 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100 dark:file:bg-orange-950/30 dark:file:text-orange-400 cursor-pointer">
    </div>
    <div>
        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Cover Image</label>
        <input type="file" name="cover_image" accept="image/*"
               class="w-full text-xs text-slate-600 dark:text-slate-400 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100 dark:file:bg-orange-950/30 dark:file:text-orange-400 cursor-pointer">
    </div>
</div>
