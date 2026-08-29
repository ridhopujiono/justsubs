@extends('justsubs::layout')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">Subscriptions</h1>
</div>

<!-- Filters -->
<div class="bg-white shadow-sm border border-gray-200 rounded-lg mb-6 p-4">
    <form action="{{ route('justsubs.subscriptions.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" id="status" class="mt-1 block w-40 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border px-3 py-2">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
            </select>
        </div>
        
        <div>
            <label for="plan_id" class="block text-sm font-medium text-gray-700">Plan</label>
            <select name="plan_id" id="plan_id" class="mt-1 block w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border px-3 py-2">
                <option value="">All Plans</option>
                @foreach($plans as $plan)
                    <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="subscriber_id" class="block text-sm font-medium text-gray-700">Subscriber ID (Exact)</label>
            <input type="text" name="subscriber_id" id="subscriber_id" value="{{ request('subscriber_id') }}" placeholder="Exact ID" class="mt-1 block w-40 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border px-3 py-2">
        </div>

        <div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">Filter</button>
            <a href="{{ route('justsubs.subscriptions.index') }}" class="ml-2 text-sm text-gray-600 hover:text-gray-900">Clear</a>
        </div>
    </form>
</div>

<!-- List -->
<div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscriber</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($subscriptions as $sub)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ $sub->subscriber_name }}</div>
                    <div class="text-xs text-gray-500">{{ $sub->subscriber_type }} #{{ $sub->subscriber_id }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ $sub->plan->name ?? 'Unknown Plan' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($sub->active())
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                    @elseif($sub->status->value === 'cancelled')
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Cancelled</span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($sub->status->value) }}</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div>{{ $sub->starts_at->format('M d, Y') }} - {{ $sub->ends_at->format('M d, Y') }}</div>
                    @if($sub->active() && $sub->cancelled())
                        <div class="text-xs text-orange-500">Cancels at period end</div>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <a href="{{ route('justsubs.subscriptions.show', $sub) }}" class="text-indigo-600 hover:text-indigo-900">Manage</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                    No subscriptions found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($subscriptions->hasPages())
    <div class="px-6 py-3 border-t border-gray-200">
        {{ $subscriptions->links() }}
    </div>
    @endif
</div>
@endsection
