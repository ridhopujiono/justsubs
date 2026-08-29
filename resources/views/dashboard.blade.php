@extends('justsubs::layout')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Overview</h1>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Total Plans -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-indigo-100 rounded-md p-3">
                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <div class="ml-4">
                <h2 class="text-sm font-medium text-gray-500">Total Plans</h2>
                <p class="text-2xl font-semibold text-gray-900">{{ number_format($total_plans) }}</p>
            </div>
        </div>
    </div>

    <!-- Active Subscriptions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-4">
                <h2 class="text-sm font-medium text-gray-500">Active Subscriptions</h2>
                <p class="text-2xl font-semibold text-gray-900">{{ number_format($active_subscriptions) }}</p>
            </div>
        </div>
    </div>

    <!-- Expiring Soon -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-4">
                <h2 class="text-sm font-medium text-gray-500">Expiring Soon (7 days)</h2>
                <p class="text-2xl font-semibold text-gray-900">{{ number_format($expiring_soon) }}</p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Getting Started</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">JustSubs package is installed correctly.</p>
    </div>
    <div class="px-6 py-5">
        <p class="text-sm text-gray-500">Select an item from the sidebar to manage your subscriptions.</p>
    </div>
</div>
@endsection
