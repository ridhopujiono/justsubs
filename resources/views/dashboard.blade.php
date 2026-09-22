@extends('justsubs::layout')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Dashboard Overview</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Snapshot of your subscription metrics.</p>
</div>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 mb-8">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-100 dark:border-gray-700 transition-colors duration-200">
        <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Active Subscriptions</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($metrics['active_subscriptions']) }}</dd>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-100 dark:border-gray-700 transition-colors duration-200">
        <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">New This Month</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($metrics['new_subscriptions']) }}</dd>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-100 dark:border-gray-700 transition-colors duration-200">
        <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-yellow-600 dark:text-yellow-500 truncate">Expiring Soon (7 Days)</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($metrics['expiring_soon']) }}</dd>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-100 dark:border-gray-700 transition-colors duration-200">
        <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-green-600 dark:text-green-400 truncate">Revenue This Month</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($metrics['revenue_this_month']) }} <span class="text-sm text-gray-400 dark:text-gray-500">IDR</span></dd>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-100 dark:border-gray-700 transition-colors duration-200">
        <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-indigo-600 dark:text-indigo-400 truncate">Estimated MRR</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($metrics['estimated_mrr']) }} <span class="text-sm text-gray-400 dark:text-gray-500">IDR</span></dd>
            <dd class="text-xs text-gray-400 dark:text-gray-500 mt-1">Normalized monthly recurring revenue</dd>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-100 dark:border-gray-700 transition-colors duration-200">
        <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-red-500 dark:text-red-400 truncate">Outstanding Invoices</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($metrics['outstanding_invoices']) }} <span class="text-sm text-gray-400 dark:text-gray-500">IDR</span></dd>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg transition-colors duration-200">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Plan Distribution</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">Breakdown of active subscriptions by plan.</p>
    </div>
    <div class="px-4 py-5 sm:p-6">
        @if(empty($planDistribution))
            <p class="text-sm text-gray-500 dark:text-gray-400">No active subscriptions to display.</p>
        @else
            <div class="space-y-4">
                @php $maxCount = max($planDistribution); @endphp
                @foreach($planDistribution as $planName => $count)
                    <div>
                        <div class="flex justify-between items-end mb-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $planName }}</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $count }}</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                            <div class="bg-indigo-600 dark:bg-indigo-500 h-2.5 rounded-full" style="width: {{ ($count / $maxCount) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
