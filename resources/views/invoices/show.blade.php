@extends('justsubs::layout')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">Invoice {{ $invoice->invoice_number }}</h1>
    <a href="{{ route('justsubs.invoices.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">&larr; Back to List</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Details Card -->
    <div class="col-span-1 md:col-span-2 space-y-6">
        <div class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-lg font-medium text-gray-900">Details</h3>
                @if($invoice->status->value === 'paid')
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">PAID</span>
                @elseif($invoice->status->value === 'unpaid')
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">UNPAID</span>
                @elseif($invoice->status->value === 'void')
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">VOID</span>
                @else
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ strtoupper($invoice->status->value) }}</span>
                @endif
            </div>
            
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Subscriber</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ \Ridho\JustSubs\JustSubs::getSubscriberName($invoice->subscriber) }}</dd>
                    <dd class="text-xs text-gray-500">{{ $invoice->subscriber_type }} #{{ $invoice->subscriber_id }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Subscription</dt>
                    @if($invoice->subscription)
                        <dd class="mt-1 text-sm text-gray-900"><a href="{{ route('justsubs.subscriptions.show', $invoice->subscription) }}" class="text-indigo-600 hover:underline">Sub #{{ $invoice->subscription->id }}</a></dd>
                        <dd class="text-xs text-gray-500">{{ $invoice->subscription->plan->name ?? 'Unknown Plan' }}</dd>
                    @else
                        <dd class="mt-1 text-sm text-gray-500">N/A</dd>
                    @endif
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Amount</dt>
                    <dd class="mt-1 text-lg font-semibold text-gray-900">{{ number_format($invoice->amount, 0) }} {{ strtoupper($invoice->currency) }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Dates</dt>
                    <dd class="mt-1 text-sm text-gray-900">Created: {{ $invoice->created_at->format('M d, Y H:i') }}</dd>
                    @if($invoice->due_at)
                    <dd class="mt-1 text-sm {{ $invoice->due_at->isPast() && $invoice->status->value === 'unpaid' ? 'text-red-600 font-semibold' : 'text-gray-900' }}">Due: {{ $invoice->due_at->format('M d, Y H:i') }}</dd>
                    @endif
                    @if($invoice->paid_at)
                    <dd class="mt-1 text-sm text-green-600 font-semibold">Paid: {{ $invoice->paid_at->format('M d, Y H:i') }}</dd>
                    @endif
                </div>
                
                @if($invoice->metadata)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Metadata</dt>
                    <dd class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded-md border border-gray-200">
                        <pre class="text-xs overflow-x-auto">{{ json_encode($invoice->metadata, JSON_PRETTY_PRINT) }}</pre>
                    </dd>
                </div>
                @endif
            </dl>
        </div>

        <div class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Payment History</h3>
            @if($invoice->payments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase">Provider</th>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($invoice->payments as $payment)
                            <tr>
                                <td class="py-3 whitespace-nowrap text-sm text-gray-500">{{ $payment->created_at->format('M d, Y H:i') }}</td>
                                <td class="py-3 whitespace-nowrap text-sm text-gray-900">{{ ucfirst($payment->provider) }}</td>
                                <td class="py-3 whitespace-nowrap text-sm text-gray-500">{{ $payment->provider_reference ?? '-' }}</td>
                                <td class="py-3 whitespace-nowrap text-sm text-gray-900">{{ number_format($payment->amount, 0) }} {{ strtoupper($payment->currency) }}</td>
                                <td class="py-3 whitespace-nowrap">
                                    @if($payment->status->value === 'success')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Success</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($payment->status->value) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500">No payment attempts recorded.</p>
            @endif
        </div>
    </div>

    <!-- Actions Sidebar -->
    <div class="col-span-1 space-y-6">
        
        @if($invoice->status->value === 'unpaid')
        <!-- Manual Payment -->
        <div class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Mark as Paid (Manual)</h3>
            <p class="text-sm text-gray-500 mb-4">Record an external or manual payment. This will activate or renew the linked subscription.</p>
            <form action="{{ route('justsubs.invoices.mark_paid', $invoice) }}" method="POST" class="space-y-3">
                @csrf
                <input type="text" name="reference" placeholder="Reference ID / Note (Optional)" class="block w-full rounded-md border-gray-300 shadow-sm border px-3 py-2 sm:text-sm">
                <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-700">Confirm Payment</button>
            </form>
            @error('reference') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        
        <!-- Void Invoice -->
        <div class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Void Invoice</h3>
            <p class="text-sm text-gray-500 mb-4">Cancel this invoice so it cannot be paid.</p>
            <form action="{{ route('justsubs.invoices.void', $invoice) }}" method="POST" onsubmit="return confirm('Are you sure you want to void this invoice?');">
                @csrf
                <button type="submit" class="w-full bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-50">Void Invoice</button>
            </form>
        </div>
        @elseif($invoice->status->value === 'paid')
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
            <svg class="mx-auto h-12 w-12 text-green-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <h3 class="text-lg font-medium text-green-800">Invoice Paid</h3>
            <p class="mt-1 text-sm text-green-600">This invoice has been successfully paid and cannot be edited or voided.</p>
        </div>
        @elseif($invoice->status->value === 'void')
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
            <h3 class="text-lg font-medium text-gray-800">Invoice Voided</h3>
            <p class="mt-1 text-sm text-gray-600">This invoice has been voided and requires no further action.</p>
        </div>
        @endif

    </div>
</div>
@endsection
