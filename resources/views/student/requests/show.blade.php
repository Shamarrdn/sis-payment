@extends('layouts.student')
@section('title', 'تفاصيل الطلب')
@section('page-title', 'تفاصيل الطلب #' . $payment->reference_number)

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="fw-bold">{{ $payment->service?->name }}</h5>
                <p class="text-muted mb-2">
                    الحالة: <span class="badge bg-primary">{{ \App\Services\PaymentRequestService::fulfillmentLabel($payment->fulfillment_status) }}</span>
                    @if($payment->service?->estimated_days)
                        — وقت التنفيذ المتوقع: {{ $payment->service->estimated_days }} أيام عمل
                    @endif
                </p>
                @if(\App\Services\PaymentRequestService::isDelayed($payment))
                    <div class="alert alert-warning py-2">هذا الطلب متأخر عن الوقت المتوقع</div>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">سجل التغييرات (Timeline)</div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    @foreach($payment->statusHistories as $h)
                        <li class="mb-3 ps-3 border-start border-3 border-primary">
                            <div class="fw-bold">{{ \App\Models\PaymentStatusHistory::statusLabel($h->status) }}</div>
                            @if($h->note)<div class="small text-muted">{{ $h->note }}</div>@endif
                            <div class="small text-muted">{{ $h->created_at->format('Y/m/d H:i') }}
                                @if($h->user) — {{ $h->user->name }} @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        @if($payment->canBeCancelledByStudent())
            <form action="{{ route('student.service-request.cancel', $payment) }}" method="POST" class="card border-0 shadow-sm mb-3" onsubmit="return confirm('إلغاء الطلب؟')">
                @csrf
                <div class="card-body">
                    <button class="btn btn-outline-danger w-100">إلغاء الطلب</button>
                    <small class="text-muted d-block mt-2">متاح قبل بدء التنفيذ من الموظف</small>
                </div>
            </form>
        @endif

        @if($payment->canBeRated())
            <form action="{{ route('student.service-request.rate', $payment) }}" method="POST" class="card border-0 shadow-sm mb-3">
                @csrf
                <div class="card-body">
                    <label class="fw-bold">قيّم الخدمة (1–5)</label>
                    <select name="rating" class="form-select mb-2" required>
                        @for($i=5;$i>=1;$i--)
                            <option value="{{ $i }}">{{ $i }} نجوم</option>
                        @endfor
                    </select>
                    <textarea name="comment" class="form-control mb-2" rows="2" placeholder="تعليق اختياري"></textarea>
                    <button class="btn btn-primary w-100">إرسال التقييم</button>
                </div>
            </form>
        @elseif($payment->rating)
            <div class="card border-0 shadow-sm mb-3"><div class="card-body">تقييمك: {{ $payment->rating }}/5</div></div>
        @endif

        <a href="{{ route('student.receipt', $payment) }}" class="btn btn-outline-secondary w-100 mb-2">عرض الإيصال</a>
        <a href="{{ route('student.requests') }}" class="btn btn-link w-100">← كل الطلبات</a>
    </div>
</div>
@endsection
