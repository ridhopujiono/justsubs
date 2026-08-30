<?php

namespace Ridho\JustSubs\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Ridho\JustSubs\Models\Payment;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with(['invoice.subscriber'])->orderBy('id', 'desc')->paginate(20);

        return view('justsubs::payments.index', compact('payments'));
    }
}
