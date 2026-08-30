@extends('justsubs::layout')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">Invoices</h1>
</div>

<!-- Filters -->
<div class="bg-white shadow-sm border border-gray-200 rounded-lg mb-6 p-4">
    <form action="{{ route('justsubs.invoices.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" id="status" class="mt-1 block w-40 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border px-3 py-2">
                <option value="">All Statuses</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="void" {{ request('status') == 'void' ? 'selected' : '' }}>Void</option>
                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
            </select>
        </div>

        <div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">Filter</button>
            <a href="{{ route('justsubs.invoices.index') }}" class="ml-2 text-sm text-gray-600 hover:text-gray-900">Clear</a>
        </div>
    </form>
</div>

<!-- List -->
<div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscriber</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created / Due</th>
                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($invoices as $invoice)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ $invoice->invoice_number }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ \Ridho\JustSubs\JustSubs::getSubscriberName($invoice->subscriber) }}</div>
                    <div class="text-xs text-gray-500">{{ $invoice->subscriber_type }} #{{ $invoice->subscriber_id }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ number_format($invoice->amount, 0) }} {{ strtoupper($invoice->currency) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($invoice->status->value === 'paid')
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                    @elseif($invoice->status->value === 'unpaid')
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Unpaid</span>
                    @elseif($invoice->status->value === 'void')
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Void</span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($invoice->status->value) }}</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div>Created: {{ $invoice->created_at->format('M d, Y') }}</div>
                    @if($invoice->due_at)
                    <div>Due: <span class="{{ $invoice->due_at->isPast() && $invoice->status->value === 'unpaid' ? 'text-red-600 font-semibold' : '' }}">{{ $invoice->due_at->format('M d, Y') }}</span></div>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <a href="{{ route('justsubs.invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-900">Manage</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                    No invoices found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($invoices->hasPages())
    <div class="px-6 py-3 border-t border-gray-200">
        {{ $invoices->links() }}
    </div>
    @endif
</div>
@endsection
