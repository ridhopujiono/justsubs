@extends('justsubs::layout')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Plans</h1>
    <a href="{{ route('justsubs.plans.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
        Create Plan
    </a>
</div>

<div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interval</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscribers</th>
                <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">Actions</span>
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($plans as $plan)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ $plan->name }}</div>
                    <div class="text-sm text-gray-500">{{ $plan->slug }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $plan->currency }} {{ number_format($plan->price) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    Every {{ $plan->interval_count }} {{ $plan->interval_unit->value }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($plan->is_active)
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            Active
                        </span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                            Inactive
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $plan->subscriptions_count }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <a href="{{ route('justsubs.plans.edit', $plan) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                    No plans found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($plans->hasPages())
    <div class="px-6 py-3 border-t border-gray-200">
        {{ $plans->links() }}
    </div>
    @endif
</div>
@endsection
