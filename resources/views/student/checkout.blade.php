@extends('layouts.auth')

@section('title', 'تأكيد الدفع - ' . $service->name)
@section('hero-subtitle', 'سداد الخدمات الجامعية')

@section('content')
<div class="auth-card">
    <div class="card-header-uni">
        <i class="bi bi-credit-card-fill" style="font-size:1.4rem;color:var(--accent)"></i>
        تأكيد عملية الدفع الألكتروني
    </div>
    <div class="card-body">
        <div class="rounded-3 p-3 mb-4" style="background:#f7f9fc;border:1px solid #e0e8f4;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted mb-1" style="font-size:0.82rem;">الخدمة المطلوبة</div>
                    <div class="fw-bold" style="color:#1a2d5a;font-size:1rem;">{{ $service->name }}</div>
                    <span class="badge rounded-pill mt-1" style="background:#e8eef6;color:#1a2d5a;">{{ $service->category ?? $service->type }}</span>
                </div>
                <div class="text-end">
                    <div class="text-muted mb-1" style="font-size:0.82rem;">المبلغ للوحدة</div>
                    <div class="fw-800" style="font-size:1.6rem;font-weight:800;color:#2e7d32;"><span id="unit-price">{{ number_format($service->amount) }}</span> ج.م</div>
                </div>
            </div>
        </div>

        <div class="mb-4 d-flex gap-3">
            <div class="flex-grow-1 rounded-3 p-3 text-center" style="background:#f7f9fc;border:1px solid #e0e8f4;">
                <div class="text-muted mb-1" style="font-size:0.78rem;">الطالب</div>
                <div class="fw-bold" style="font-size:0.95rem;">{{ auth()->guard('student')->user()->name }}</div>
            </div>
            <div class="flex-grow-1 rounded-3 p-3 text-center" style="background:#f7f9fc;border:1px solid #e0e8f4;">
                <div class="text-muted mb-1" style="font-size:0.78rem;">الرقم القومي</div>
                <div class="fw-bold" style="font-size:0.95rem;">{{ auth()->guard('student')->user()->national_id }}</div>
            </div>
        </div>

        <form action="{{ route('student.pay', $service) }}" method="POST">
            @csrf

            @if($errors->any())
                <div class="alert alert-danger rounded-3 mb-3">
                    @foreach($errors->all() as $error)
                        <div><i class="bi bi-exclamation-circle-fill me-1"></i>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if($service->allows_quantity)
            <div class="mb-4">
                <label class="form-label text-primary fw-bold">العدد/الكمية المطلوبة <span class="text-danger">*</span></label>
                <input type="number" id="quantity" name="quantity" min="1" max="10" value="1" class="form-control" required onchange="calculateTotal()">
            </div>
            @endif

            <div class="mb-4">
                <label class="form-label text-primary fw-bold">طريقة الدفع <span class="text-danger">*</span></label>
                <select name="payment_method" class="form-select" required>
                    <option value="Visa">فيزا / ماستركارد (Visa)</option>
                    <option value="Fawry">فوري (Fawry)</option>
                </select>
            </div>

            @if(!empty($service->sub_options))
                <div class="mb-4">
                    <label class="form-label text-primary fw-bold">اختر النوع المطلوب <span class="text-danger">*</span></label>
                    <div class="d-flex flex-column gap-2">
                        @foreach($service->sub_options as $option)
                            <label class="d-flex align-items-center gap-3 p-3 rounded-3 cursor-pointer"
                                   style="background:#f7f9fc;border:1.5px solid #dde4f0;cursor:pointer;">
                                <input type="radio" name="notes" value="{{ $option }}" required class="form-check-input mt-0" style="width:18px;height:18px;">
                                <span class="fw-500">{{ $option }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @elseif($service->requires_subject)
                <div class="mb-4">
                    <label class="form-label fw-bold text-danger">اسم المادة / ملاحظة <span class="text-danger">*</span></label>
                    <input type="text" class="form-control border-danger" name="notes"
                           placeholder="أدخل الملاحظات..." required>
                </div>
            @endif

            <div class="alert alert-info d-flex justify-content-between my-4">
                <span class="fw-bold">الإجمالي المطلوب:</span>
                <span class="fw-bold fs-5"><span id="total-price">{{ number_format($service->amount) }}</span> ج.م</span>
            </div>

            <button type="submit" class="btn-signin" style="background:#2e7d32;">
                <i class="bi bi-credit-card me-2"></i> تأكيد الدفع الآن
            </button>
        </form>

        <div class="text-center mt-3">
            <a href="{{ route('student.dashboard') }}" class="text-muted text-decoration-none" style="font-size:0.9rem;">
                <i class="bi bi-arrow-right me-1"></i> العودة للرئيسية
            </a>
        </div>
    </div>
</div>

<script>
    const basePrice = {{ $service->amount }};
    function calculateTotal() {
        const qtyElement = document.getElementById('quantity');
        const qty = qtyElement ? parseInt(qtyElement.value) : 1;
        document.getElementById('total-price').innerText = new Intl.NumberFormat().format(basePrice * qty);
    }
</script>
@endsection
