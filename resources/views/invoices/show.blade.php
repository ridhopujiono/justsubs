@extends('justsubs::layout')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div class="flex items-center space-x-3">
        <a href="{{ route('justsubs.invoices.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
            &larr; Back
        </a>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Invoice: {{ $invoice->invoice_number }}</h1>
        
        @if($invoice->status->value === 'paid')
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 border border-transparent dark:border-green-800">Paid</span>
        @elseif($invoice->status->value === 'unpaid')
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 border border-transparent dark:border-yellow-800">Unpaid</span>
        @elseif($invoice->status->value === 'void')
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-600">Void</span>
        @else
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-600">{{ ucfirst($invoice->status->value) }}</span>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Details -->
    <div class="col-span-2 space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6 transition-colors duration-200">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Invoice Details</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Subscriber</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ \Ridho\JustSubs\JustSubs::getSubscriberName($invoice->subscriber) }}</dd>
                    <dd class="text-xs text-gray-500 dark:text-gray-400">{{ class_basename($invoice->subscriber_type) }} #{{ $invoice->subscriber_id }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Subscription</dt>
                    @if($invoice->subscription)
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100"><a href="{{ route('justsubs.subscriptions.show', $invoice->subscription) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Sub #{{ $invoice->subscription->id }}</a></dd>
                        <dd class="text-xs text-gray-500 dark:text-gray-400">{{ $invoice->subscription->plan->name ?? 'Unknown Plan' }}</dd>
                    @else
                        <dd class="mt-1 text-sm text-gray-500 dark:text-gray-400">N/A</dd>
                    @endif
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Amount</dt>
                    <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ number_format($invoice->amount, 0) }} {{ strtoupper($invoice->currency) }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Dates</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">Created: {{ $invoice->created_at->format('M d, Y H:i') }}</dd>
                    @if($invoice->due_at)
                    <dd class="mt-1 text-sm {{ $invoice->due_at->isPast() && $invoice->status->value === 'unpaid' ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-900 dark:text-gray-100' }}">Due: {{ $invoice->due_at->format('M d, Y H:i') }}</dd>
                    @endif
                    @if($invoice->paid_at)
                    <dd class="mt-1 text-sm text-green-600 dark:text-green-400 font-semibold">Paid: {{ $invoice->paid_at->format('M d, Y H:i') }}</dd>
                    @endif
                </div>
                
                @if($invoice->metadata)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Metadata</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-900 p-3 rounded-md border border-gray-200 dark:border-gray-700">
                        <pre class="text-xs overflow-x-auto">{{ json_encode($invoice->metadata, JSON_PRETTY_PRINT) }}</pre>
                    </dd>
                </div>
                @endif
            </dl>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6 transition-colors duration-200">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Payment History</h3>
            @if($invoice->payments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Provider</th>
                                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Reference</th>
                                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($invoice->payments as $payment)
                            <tr>
                                <td class="py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $payment->created_at->format('M d, Y H:i') }}</td>
                                <td class="py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($payment->provider) }}</td>
                                <td class="py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $payment->provider_reference ?? '-' }}</td>
                                <td class="py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ number_format($payment->amount, 0) }} {{ strtoupper($payment->currency) }}</td>
                                <td class="py-3 whitespace-nowrap">
                                    @if($payment->status->value === 'success')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 border border-transparent dark:border-green-800">Success</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-600">{{ ucfirst($payment->status->value) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">No payment attempts recorded.</p>
            @endif
        </div>
    </div>

    <!-- Actions Sidebar -->
    <div class="col-span-1 space-y-6">
        
        @if($invoice->status->value === 'unpaid')
        <!-- Manual Payment -->
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6 transition-colors duration-200">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Mark as Paid (Manual)</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Record an external or manual payment. This will activate or renew the linked subscription.</p>
            <form action="{{ route('justsubs.invoices.mark_paid', $invoice) }}" method="POST" class="space-y-3">
                @csrf
                <input type="text" name="reference" placeholder="Reference ID / Note (Optional)" class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm border px-3 py-2 sm:text-sm">
                <button type="submit" class="w-full bg-green-600 border border-transparent text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-700 dark:hover:bg-green-500">Confirm Payment</button>
            </form>
            @error('reference') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>
        
        <!-- Void Invoice -->
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6 transition-colors duration-200">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Void Invoice</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Cancel this invoice so it cannot be paid.</p>
            <form action="{{ route('justsubs.invoices.void', $invoice) }}" method="POST" onsubmit="return confirm('Are you sure you want to void this invoice?');">
                @csrf
                <button type="submit" class="w-full bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700">Void Invoice</button>
            </form>
        </div>
        @elseif($invoice->status->value === 'paid')
        <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800/50 rounded-lg p-6 text-center transition-colors duration-200">
            <svg class="mx-auto h-12 w-12 text-green-500 dark:text-green-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <h3 class="text-lg font-medium text-green-800 dark:text-green-400">Invoice Paid</h3>
            <p class="mt-1 text-sm text-green-600 dark:text-green-500">This invoice has been successfully paid and cannot be edited or voided.</p>
        </div>
        @elseif($invoice->status->value === 'void')
        <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-6 text-center transition-colors duration-200">
            <h3 class="text-lg font-medium text-gray-800 dark:text-gray-400">Invoice Voided</h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-500">This invoice has been voided and requires no further action.</p>
        </div>
        @endif

    </div>
</div>
@endsection
