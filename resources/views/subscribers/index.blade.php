@extends('justsubs::layout')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Subscribers</h1>
    <p class="text-sm text-gray-500 mt-1">Subscribers are automatically listed here when they create a subscription or invoice.</p>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Subscriber
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Type
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Active Plan
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Total Invoices
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($subscribers as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $item->resolved_name }}
                            </div>
                            <div class="text-xs text-gray-500">
                                ID: {{ $item->subscriber_id }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                {{ class_basename($item->subscriber_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($item->active_subscription)
                                <div class="text-sm text-gray-900">
                                    {{ $item->active_subscription->plan->name ?? 'Unknown Plan' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Expires: {{ $item->active_subscription->ends_at->format('M d, Y') }}
                                </div>
                            @else
                                <span class="text-sm text-gray-500 italic">No active subscription</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->total_invoices }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            No subscribers found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($subscribers->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $subscribers->links() }}
        </div>
    @endif
</div>
@endsection
