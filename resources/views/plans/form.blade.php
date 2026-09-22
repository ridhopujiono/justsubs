<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="col-span-1">
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Plan Name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $plan->name ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm border px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>

    <div class="col-span-1">
        <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Slug</label>
        <input type="text" name="slug" id="slug" value="{{ old('slug', $plan->slug ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm border px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        @error('slug') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>

    <div class="col-span-2">
        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
        <textarea name="description" id="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm border px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $plan->description ?? '') }}</textarea>
        @error('description') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>

    <div class="col-span-1">
        <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price (integer)</label>
        <input type="number" name="price" id="price" value="{{ old('price', $plan->price ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm border px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Do not use decimals.</p>
        @error('price') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>

    <div class="col-span-1">
        <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency</label>
        <input type="text" name="currency" id="currency" value="{{ old('currency', $plan->currency ?? 'IDR') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm border px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        @error('currency') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>

    <div class="col-span-1">
        <label for="interval_count" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Interval Count</label>
        <input type="number" name="interval_count" id="interval_count" value="{{ old('interval_count', $plan->interval_count ?? 1) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm border px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        @error('interval_count') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>

    <div class="col-span-1">
        <label for="interval_unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Interval Unit</label>
        <select name="interval_unit" id="interval_unit" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm border px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            @php $currentUnit = old('interval_unit', isset($plan) && $plan->interval_unit ? $plan->interval_unit->value : 'month'); @endphp
            <option value="day" {{ $currentUnit === 'day' ? 'selected' : '' }}>Day</option>
            <option value="week" {{ $currentUnit === 'week' ? 'selected' : '' }}>Week</option>
            <option value="month" {{ $currentUnit === 'month' ? 'selected' : '' }}>Month</option>
            <option value="year" {{ $currentUnit === 'year' ? 'selected' : '' }}>Year</option>
        </select>
        @error('interval_unit') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>

    <div class="col-span-2">
        <label for="features" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Features</label>
        <textarea name="features" id="features" rows="4" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm border px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="One feature per line">{{ old('features', isset($plan) && is_array($plan->features) ? implode("\n", $plan->features) : '') }}</textarea>
        @error('features') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>

    <div class="col-span-2">
        <div class="flex items-start">
            <div class="flex items-center h-5">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }} class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded">
            </div>
            <div class="ml-3 text-sm">
                <label for="is_active" class="font-medium text-gray-700 dark:text-gray-300">Active</label>
                <p class="text-gray-500 dark:text-gray-400">Uncheck to deactivate this plan (preventing new subscriptions).</p>
            </div>
        </div>
        @error('is_active') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>
</div>
