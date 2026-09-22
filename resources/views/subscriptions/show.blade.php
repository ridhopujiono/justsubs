@extends('justsubs::layout')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div class="flex items-center space-x-3">
        <a href="{{ route('justsubs.subscriptions.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
            &larr; Back
        </a>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Subscription #{{ $subscription->id }}</h1>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Details -->
    <div class="col-span-2 space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6 transition-colors duration-200">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Subscription Details</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Subscriber</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $subscription->subscriber_name }}</dd>
                    <dd class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ class_basename($subscription->subscriber_type) }} #{{ $subscription->subscriber_id }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Plan</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $subscription->plan->name ?? 'Unknown Plan' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        @if($subscription->active())
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 border border-transparent dark:border-green-800">Active</span>
                        @elseif($subscription->status->value === 'cancelled')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 border border-transparent dark:border-red-800">Cancelled</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-600">{{ ucfirst($subscription->status->value) }}</span>
                        @endif
                        
                        @if($subscription->active() && $subscription->cancelled())
                            <span class="ml-2 text-xs text-orange-600 dark:text-orange-400 font-medium">Cancels at period end</span>
                        @endif
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Period</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ $subscription->starts_at->format('M d, Y H:i') }} - {{ $subscription->ends_at->format('M d, Y H:i') }}
                    </dd>
                    <dd class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        @if($subscription->ends_at->isFuture())
                            Ends in {{ $subscription->ends_at->diffForHumans() }}
                        @else
                            Ended {{ $subscription->ends_at->diffForHumans() }}
                        @endif
                    </dd>
                </div>
                
                @if($subscription->cancelled_at)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cancelled At</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $subscription->cancelled_at->format('M d, Y H:i') }}</dd>
                </div>
                @endif
            </dl>
        </div>

        @if($subscription->metadata && isset($subscription->metadata['extensions']))
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6 transition-colors duration-200">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Extension History</h3>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($subscription->metadata['extensions'] as $ext)
                <li class="py-3">
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">+{{ $ext['count'] }} {{ $ext['unit'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Reason: {{ $ext['reason'] ?? 'N/A' }}</div>
                    <div class="text-xs text-gray-400 dark:text-gray-500">{{ \Carbon\Carbon::parse($ext['extended_at'])->format('M d, Y H:i') }}</div>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <!-- Actions Sidebar -->
    <div class="col-span-1 space-y-6">
        
        <!-- Renew -->
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6 transition-colors duration-200">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Renew</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Renew subscription for another period. If active, time is appended.</p>
            <form action="{{ route('justsubs.subscriptions.renew', $subscription) }}" method="POST">
                @csrf
                <button type="submit" class="w-full bg-indigo-600 border border-transparent text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 dark:hover:bg-indigo-500">Renew Subscription</button>
            </form>
        </div>

        <!-- Extend -->
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6 transition-colors duration-200">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Extend (Manual)</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Add arbitrary time without charging.</p>
            <form action="{{ route('justsubs.subscriptions.extend', $subscription) }}" method="POST" class="space-y-3">
                @csrf
                <div class="flex space-x-2">
                    <input type="number" name="count" min="1" value="1" required class="block w-20 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm border px-3 py-1 sm:text-sm">
                    <select name="unit" required class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm border px-3 py-1 sm:text-sm">
                        <option value="day">Days</option>
                        <option value="week">Weeks</option>
                        <option value="month">Months</option>
                        <option value="year">Years</option>
                    </select>
                </div>
                <input type="text" name="reason" placeholder="Reason (Optional)" class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm border px-3 py-1 sm:text-sm">
                <button type="submit" class="w-full bg-gray-800 dark:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-900 dark:hover:bg-gray-500">Extend Time</button>
            </form>
            @error('count') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            @error('unit') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <!-- Change Plan -->
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6 transition-colors duration-200">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Change Plan</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Starts a new billing cycle immediately without proration.</p>
            <form action="{{ route('justsubs.subscriptions.change_plan', $subscription) }}" method="POST" class="space-y-3">
                @csrf
                <select name="plan_id" required class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm border px-3 py-1 sm:text-sm">
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full bg-gray-800 dark:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-900 dark:hover:bg-gray-500">Change Plan</button>
            </form>
            @error('plan_id') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <!-- Cancel / Terminate -->
        @if(!$subscription->cancelled() || $subscription->active())
        <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800/50 rounded-lg p-6 transition-colors duration-200">
            <h3 class="text-lg font-medium text-red-800 dark:text-red-400 mb-2">Danger Zone</h3>
            
            @if(!$subscription->cancelled())
            <form action="{{ route('justsubs.subscriptions.cancel', $subscription) }}" method="POST" class="mb-4">
                @csrf
                <input type="hidden" name="immediately" value="0">
                <button type="submit" class="w-full bg-white dark:bg-gray-800 text-red-700 dark:text-red-400 border border-red-300 dark:border-red-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-red-50 dark:hover:bg-red-900/50">Cancel at Period End</button>
            </form>
            @endif

            @if($subscription->active())
            <form action="{{ route('justsubs.subscriptions.cancel', $subscription) }}" method="POST" onsubmit="return confirm('Are you sure you want to terminate this subscription immediately? They will lose access instantly.');">
                @csrf
                <input type="hidden" name="immediately" value="1">
                <button type="submit" class="w-full bg-red-600 border border-transparent text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-red-700 dark:hover:bg-red-500">Terminate Immediately</button>
            </form>
            @endif
        </div>
        @endif

    </div>
</div>
@endsection
