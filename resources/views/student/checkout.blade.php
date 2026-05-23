@extends('layouts.auth')

@section('title', 'مراجعة واعتماد الرسوم - ' . $service->name)
@section('hero-subtitle', 'يرجى مراجعة تفاصيل الرسوم قبل الانتقال لبوابة الدفع الإلكترونية')

@section('content')
<div class="auth-card" style="border-top: 4px solid var(--primary);">
    <div class="card-header-uni" style="border-bottom: 1px solid #e5eaf2; padding-bottom: 15px; margin-bottom: 20px;">
        <i class="bi bi-file-earmark-text" style="font-size:1.4rem;color:var(--primary)"></i>
        ملخص تفاصيل الرسوم
    </div>
    <div class="card-body">
        <div class="rounded-3 p-4 mb-4" style="background:#fff; border:1px solid #e2e8f0;">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                <div>
                    <div class="text-muted fw-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">الخدمة المطلوبة</div>
                    <div class="fw-bold" style="color:var(--primary);font-size:1.1rem;">{{ $service->name }}</div>
                </div>
                <div class="text-end">
                    <span class="badge rounded-pill bg-light text-dark border px-3 py-2">{{ $service->category ?? $service->type }}</span>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted fw-bold" style="font-size:0.8rem; text-transform:uppercase;">القيمة المقررة للوحدة</div>
                <div class="fw-800" style="font-size:1.4rem;font-weight:800;color:#10b981;"><span id="unit-price">{{ number_format($service->amount) }}</span> ج.م</div>
            </div>
        </div>

        <div class="mb-4 row g-3">
            <div class="col-6">
                <div class="rounded-3 p-3 h-100" style="background:#f8fafc; border:1px solid #e2e8f0;">
                    <div class="text-muted mb-1" style="font-size:0.75rem; font-weight:600;">اسم الطالب</div>
                    <div class="fw-bold text-dark" style="font-size:0.9rem;">{{ auth()->guard('student')->user()->name }}</div>
                </div>
            </div>
            <div class="col-6">
                <div class="rounded-3 p-3 h-100" style="background:#f8fafc; border:1px solid #e2e8f0;">
                    <div class="text-muted mb-1" style="font-size:0.75rem; font-weight:600;">الرقم القومي</div>
                    <div class="fw-bold text-dark font-monospace" style="font-size:0.9rem;">{{ auth()->guard('student')->user()->national_id }}</div>
                </div>
            </div>
        </div>

        @if($service->instructions)
            <div class="alert alert-info rounded-3 mb-4">
                <div class="fw-bold mb-1"><i class="bi bi-info-circle me-1"></i> تعليمات الخدمة</div>
                <div style="white-space:pre-wrap;">{{ $service->instructions }}</div>
            </div>
        @endif
        @if($service->estimated_days)
            <p class="small text-muted mb-3"><i class="bi bi-clock"></i> وقت التنفيذ المتوقع: <strong>{{ $service->estimated_days }} أيام عمل</strong></p>
        @endif

        <form action="{{ route('student.pay', $service) }}" method="POST">
            @csrf

            @foreach($service->required_fields ?? [] as $field)
                @php $key = $field['key'] ?? ''; $label = $field['label'] ?? $key; @endphp
                @if($key)
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ $label }} @if($field['required'] ?? true)<span class="text-danger">*</span>@endif</label>
                    <input type="text" name="request_fields[{{ $key }}]" class="form-control" value="{{ old('request_fields.'.$key) }}" @if($field['required'] ?? true) required @endif>
                </div>
                @endif
            @endforeach

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

            <div class="alert mt-4 d-flex justify-content-between align-items-center" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #065f46; border-radius: 12px; padding: 20px;">
                <span class="fw-bold" style="font-size: 1.1rem;">إجمالي المستحق للسداد:</span>
                <span class="fw-bold fs-4"><span id="total-price">{{ number_format($service->amount) }}</span> ج.م</span>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-3 mt-2 rounded-3 fw-bold" style="font-size: 1.1rem;">
                <i class="bi bi-shield-lock me-2"></i> الانتقال لبوابة الدفع (E-finance)
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
