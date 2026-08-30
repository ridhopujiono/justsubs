@extends('justsubs::layout')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Dashboard Overview</h1>
    <p class="text-sm text-gray-500 mt-1">Snapshot of your subscription metrics.</p>
</div>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 mb-8">
    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-100">
        <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">Active Subscriptions</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($metrics['active_subscriptions']) }}</dd>
        </div>
    </div>
    
    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-100">
        <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">New This Month</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($metrics['new_subscriptions']) }}</dd>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-100">
        <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate text-yellow-600">Expiring Soon (7 Days)</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($metrics['expiring_soon']) }}</dd>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-100">
        <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate text-green-600">Revenue This Month</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($metrics['revenue_this_month']) }} <span class="text-sm text-gray-400">IDR</span></dd>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-100">
        <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate text-indigo-600">Estimated MRR</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($metrics['estimated_mrr']) }} <span class="text-sm text-gray-400">IDR</span></dd>
            <dd class="text-xs text-gray-400 mt-1">Normalized monthly recurring revenue</dd>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-100">
        <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate text-red-500">Outstanding Invoices</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($metrics['outstanding_invoices']) }} <span class="text-sm text-gray-400">IDR</span></dd>
        </div>
    </div>
</div>

<div class="bg-white shadow-sm border border-gray-200 rounded-lg">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Plan Distribution</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">Breakdown of active subscriptions by plan.</p>
    </div>
    <div class="px-4 py-5 sm:p-6">
        @if(empty($planDistribution))
            <p class="text-sm text-gray-500">No active subscriptions to display.</p>
        @else
            <div class="space-y-4">
                @php $maxCount = max($planDistribution); @endphp
                @foreach($planDistribution as $planName => $count)
                    <div>
                        <div class="flex justify-between items-end mb-1">
                            <span class="text-sm font-medium text-gray-700">{{ $planName }}</span>
                            <span class="text-sm font-medium text-gray-900">{{ $count }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ ($count / $maxCount) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
