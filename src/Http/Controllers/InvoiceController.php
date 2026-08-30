<?php

namespace Ridho\JustSubs\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Ridho\JustSubs\Enums\InvoiceStatus;
use Ridho\JustSubs\Exceptions\PaymentFailedException;
use Ridho\JustSubs\JustSubs;
use Ridho\JustSubs\Models\Invoice;
use Ridho\JustSubs\Services\BillingManager;

class InvoiceController extends Controller
{
    protected BillingManager $manager;

    public function __construct(BillingManager $manager)
    {
        $this->manager = $manager;
    }

    public function index(Request $request)
    {
        $query = Invoice::with(['subscriber'])->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->paginate(20)->withQueryString();

        return view('justsubs::invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['subscriber', 'subscription.plan', 'payments' => function ($q) {
            $q->orderBy('created_at', 'desc');
        }]);

        return view('justsubs::invoices.show', compact('invoice'));
    }

    public function markPaid(Request $request, Invoice $invoice)
    {
        $request->validate([
            'reference' => 'nullable|string|max:255',
        ]);

        try {
            $driverName = $request->input('driver', 'manual');
            $driver = JustSubs::getPaymentDriver($driverName);
            $metadata = [];

            if ($request->filled('reference')) {
                $metadata['reference'] = $request->reference;
            }

            $this->manager->processPayment(
                $invoice,
                $driver,
                $invoice->amount,
                $invoice->currency,
                $metadata
            );

            return redirect()->route('justsubs.invoices.show', $invoice)
                ->with('success', 'Payment received and invoice marked as paid.');
        } catch (PaymentFailedException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function void(Invoice $invoice)
    {
        if ($invoice->status === InvoiceStatus::Paid) {
            return redirect()->back()->with('error', 'Cannot void an already paid invoice.');
        }

        $this->manager->voidInvoice($invoice);

        return redirect()->route('justsubs.invoices.show', $invoice)
            ->with('success', 'Invoice has been voided.');
    }
}
