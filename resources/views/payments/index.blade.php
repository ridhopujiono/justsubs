@extends('justsubs::layout')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">Payments</h1>
</div>

<div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment ID</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscriber</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status / Date</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($payments as $payment)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    #{{ $payment->id }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <a href="{{ route('justsubs.invoices.show', $payment->invoice) }}" class="text-indigo-600 hover:underline font-medium text-sm">
                        {{ $payment->invoice->invoice_number }}
                    </a>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ \Ridho\JustSubs\JustSubs::getSubscriberName($payment->invoice->subscriber) }}</div>
                    <div class="text-xs text-gray-500">{{ $payment->invoice->subscriber_type }} #{{ $payment->invoice->subscriber_id }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ ucfirst($payment->provider) }}
                    @if($payment->provider_reference)
                        <div class="text-xs text-gray-500">Ref: {{ $payment->provider_reference }}</div>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ number_format($payment->amount, 0) }} {{ strtoupper($payment->currency) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($payment->status->value === 'success')
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Success</span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($payment->status->value) }}</span>
                    @endif
                    <div class="text-xs text-gray-500 mt-1">{{ $payment->created_at->format('M d, Y H:i') }}</div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                    No payments found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($payments->hasPages())
    <div class="px-6 py-3 border-t border-gray-200">
        {{ $payments->links() }}
    </div>
    @endif
</div>
@endsection
