<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceRequestController extends Controller
{
    public function show(Payment $payment)
    {
        $student = Auth::guard('student')->user();
        if ($payment->student_id !== $student->id) {
            abort(403);
        }

        $payment->load(['service', 'statusHistories.user']);

        return view('student.requests.show', compact('payment'));
    }

    public function cancel(Payment $payment)
    {
        $student = Auth::guard('student')->user();
        if ($payment->student_id !== $student->id) {
            abort(403);
        }

        if (!PaymentRequestService::cancelByStudent($payment)) {
            return back()->withErrors(['error' => 'لا يمكن إلغاء الطلب بعد بدء التنفيذ.']);
        }

        return redirect()->route('student.requests')
            ->with('success', 'تم إلغاء الطلب بنجاح.');
    }

    public function rate(Request $request, Payment $payment)
    {
        $student = Auth::guard('student')->user();
        if ($payment->student_id !== $student->id || !$payment->canBeRated()) {
            abort(403);
        }

        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        PaymentRequestService::rate($payment, $validated['rating'], $validated['comment'] ?? null);

        return back()->with('success', 'شكراً لتقييمك!');
    }
}
