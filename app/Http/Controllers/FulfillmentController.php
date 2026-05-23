<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentRequestService;
use Illuminate\Http\Request;

class FulfillmentController extends Controller
{
    public function start(Payment $payment)
    {
        if ($payment->status !== 'paid' || $payment->fulfillment_status !== 'awaiting_processing') {
            return back()->withErrors(['error' => 'لا يمكن بدء تنفيذ هذا الطلب.']);
        }

        PaymentRequestService::start($payment, auth()->user());

        return back()->with('success', 'تم بدء تنفيذ الطلب.');
    }

    public function complete(Payment $payment)
    {
        if ($payment->fulfillment_status !== 'in_progress') {
            return back()->withErrors(['error' => 'يجب أن يكون الطلب قيد التنفيذ أولاً.']);
        }

        PaymentRequestService::complete($payment, auth()->user());

        return back()->with('success', 'تم إكمال الطلب.');
    }
}
