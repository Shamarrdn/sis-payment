@extends('layouts.auth')

@section('title', 'إيصال دفع - ' . $payment->reference_number)
@section('hero-subtitle', 'إيصال دفع إلكتروني')

@section('content')
<div class="auth-card" id="printable-receipt" style="max-width: 620px; padding: 2rem;">

    {{-- Header --}}
    <div class="text-center mb-4 border-bottom pb-4">
        <h4 class="fw-bold" style="color: #1a2d5a;">
            {{ \App\Models\SystemSetting::get('university_name', 'جامعة المستقبل') }}
        </h4>
        <div class="text-muted mt-1" style="font-size: 0.85rem;">
            {{ \App\Models\SystemSetting::get('university_address', 'القاهرة، مصر') }}
        </div>
        <div class="mt-3 d-flex justify-content-center gap-4 align-items-center">
            <div>
                <div class="text-muted small">رقم الإيصال</div>
                <div class="fw-bold font-monospace">{{ $payment->reference_number }}</div>
            </div>
            <div>
                <div class="text-muted small">تاريخ العملية</div>
                <div class="fw-bold">{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d H:i') }}</div>
            </div>
            <div>
                <span class="badge bg-success px-3 py-2 fs-6">✓ عملية ناجحة</span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Left Column: Details --}}
        <div class="col-md-8">
            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">بيانات الطالب</h6>
            <table class="table table-borderless table-sm mb-4">
                <tr><td class="text-muted w-40">اسم الطالب:</td><td class="fw-bold">{{ auth()->guard('student')->user()->name }}</td></tr>
                <tr><td class="text-muted">الرقم القومي:</td><td class="fw-bold ltr">{{ substr(auth()->guard('student')->user()->national_id, 0, 4) . '***' . substr(auth()->guard('student')->user()->national_id, -3) }}</td></tr>
                <tr><td class="text-muted">الكلية / البرنامج:</td><td class="fw-bold">{{ auth()->guard('student')->user()->college ?? '—' }} | {{ auth()->guard('student')->user()->program ?? '—' }}</td></tr>
                <tr><td class="text-muted">الفئة:</td><td class="fw-bold">{{ auth()->guard('student')->user()->user_category }}</td></tr>
                <tr><td class="text-muted">العام الدراسي:</td><td class="fw-bold">{{ auth()->guard('student')->user()->academic_year }}</td></tr>
            </table>

            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">بيانات الخدمة</h6>
            <table class="table table-borderless table-sm mb-4">
                <tr><td class="text-muted w-40">اسم الخدمة:</td><td class="fw-bold">{{ $payment->service->name }}</td></tr>
                @if($payment->notes)
                <tr><td class="text-muted">ملاحظات:</td><td class="fw-bold">{{ $payment->notes }}</td></tr>
                @endif
                <tr><td class="text-muted">سعر الوحدة:</td><td class="fw-bold">{{ number_format($payment->amount) }} ج.م</td></tr>
                <tr><td class="text-muted">الكمية:</td><td class="fw-bold">{{ $payment->quantity }}</td></tr>
                <tr><td class="text-muted">طريقة الدفع:</td><td class="fw-bold">{{ $payment->payment_method ?? 'إلكتروني' }}</td></tr>
            </table>

            <div class="alert alert-success d-flex justify-content-between rounded-3 p-3">
                <span class="fw-bold fs-5">إجمالي المدفوع:</span>
                <span class="fw-bold fs-4">{{ number_format($payment->total_amount) }} ج.م</span>
            </div>
        </div>

        {{-- Right Column: QR Code --}}
        <div class="col-md-4 d-flex flex-column align-items-center justify-content-start pt-2">
            <div class="p-3 border rounded-3 text-center" style="background: #fafafa;">
                {{-- QR Code generated via Google Charts API (no package needed) --}}
                @php
                    $verifyUrl = route('student.receipt', $payment->id);
                    $qrData    = urlencode($verifyUrl);
                @endphp
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ $qrData }}"
                     alt="QR Code للتحقق"
                     width="120" height="120"
                     style="image-rendering: pixelated;">
                <div class="text-muted mt-2" style="font-size: 0.72rem;">امسح للتحقق من صحة الإيصال</div>
            </div>
            <div class="mt-3 text-center">
                <div class="text-muted small mb-1">كود التحقق</div>
                <div class="fw-bold font-monospace border rounded px-3 py-1" style="letter-spacing: 2px; font-size: 0.85rem;">
                    {{ strtoupper(substr(md5($payment->reference_number . $payment->id), 0, 8)) }}
                </div>
            </div>
        </div>
    </div>

    <div class="text-center text-muted mt-4 pt-3 border-top" style="font-size: 0.78rem;">
        <p class="mb-1">{{ \App\Models\SystemSetting::get('receipt_footer_note', 'احتفظ برقم الإيصال للمراجعة.') }}</p>
        <p class="mb-0">للاستفسار: {{ \App\Models\SystemSetting::get('support_phone', '') }} | {{ \App\Models\SystemSetting::get('support_email', '') }}</p>
    </div>

    <div class="text-center mt-4 d-flex justify-content-center gap-3 d-print-none">
        <button onclick="window.print()" class="btn btn-outline-primary px-4 py-2">
            <i class="bi bi-printer me-2"></i> طباعة الإيصال
        </button>
        <a href="{{ route('student.dashboard') }}" class="btn btn-light px-4 py-2">
            العودة للرئيسية
        </a>
    </div>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    #printable-receipt, #printable-receipt * { visibility: visible; }
    #printable-receipt { position: absolute; left: 0; top: 0; width: 100%; border: none !important; box-shadow: none !important; }
    .d-print-none { display: none !important; }
}
</style>
@endsection
