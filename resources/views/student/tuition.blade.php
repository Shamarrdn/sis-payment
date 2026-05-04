@extends('layouts.auth')

@section('title', 'المصاريف الدراسية')
@section('hero-subtitle', 'سداد المصاريف الدراسية')

@section('content')
<div class="auth-card">
    <div class="card-header-uni" style="flex-direction:column;padding:22px;text-align:center;background:linear-gradient(135deg,#4a148c,#7b1fa2);">
        <i class="bi bi-cash-stack" style="font-size:2rem;color:#e1bee7;margin-bottom:6px;"></i>
        <div style="font-size:1.1rem;font-weight:700;">المصاريف الدراسية</div>
        <div style="font-size:0.82rem;color:rgba(255,255,255,0.7);">الإجمالي المستحق: {{ number_format($resolution['total']) }} ج.م</div>
    </div>

    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger rounded-3 mb-4">
                @foreach($errors->all() as $error)
                    <div><i class="bi bi-exclamation-circle-fill me-1"></i>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        {{-- Dynamic Resolution Info --}}
        <div class="alert alert-info rounded-3 mb-4 p-3 d-flex align-items-start gap-3 border-0 shadow-sm" style="background:#f0f7ff;">
            <i class="bi bi-info-circle-fill text-primary mt-1"></i>
            <div class="small flex-grow-1">
                <div class="fw-bold text-dark">تفاصيل الحساب:</div>
                <p class="mb-1 text-muted">تم تحديد المصروفات بناءً على: <strong>{{ $resolution['resolved_by'] }}</strong></p>
                
                @if($resolution['discount_name'])
                    <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
                        <span class="text-success"><i class="bi bi-gift-fill me-1"></i> تم تطبيق إعفاء/منحة: <strong>{{ $resolution['discount_name'] }}</strong></span>
                        <span class="badge bg-success">- {{ number_format($resolution['discount_amount']) }} ج.م</span>
                    </div>
                    <div class="x-small text-muted mt-1" style="font-size: 0.75rem;">
                         السعر الأصلي: {{ number_format($resolution['original_tuition']) }} ج.م
                    </div>
                @endif
            </div>
        </div>

        @if($paidInst1)
            <div class="alert alert-success rounded-3 mb-4">
                <i class="bi bi-check-circle-fill me-2"></i>
                لقد سبق لك سداد <strong>القسط الأول ({{ number_format($resolution['inst1_amount']) }} ج.م)</strong> — يمكنك الآن سداد القسط الثاني.
            </div>
        @endif

        <form action="{{ route('student.tuition.pay') }}" method="POST">
            @csrf

            <p class="fw-bold mb-3" style="color:#4a148c;">اختر الجزء المطلوب سداده:</p>

            <div class="d-flex flex-column gap-3 mb-4">
                {{-- Option 1: Full Payment --}}
                @if(!$paidInst1)
                <label class="d-flex align-items-start gap-3 p-4 rounded-3"
                       style="background:#faf5ff;border:2px solid #ce93d8;cursor:pointer;transition:all 0.2s;"
                       id="label-full">
                    <input type="radio" name="choice" value="full" required
                           class="form-check-input mt-1" style="width:20px;height:20px;accent-color:#7b1fa2;"
                           onchange="updateStyle()">
                    <div>
                        <div class="fw-bold" style="color:#4a148c;">الدفع الكامل دفعة واحدة</div>
                        <div class="text-muted" style="font-size:0.85rem;">إجمالي كامل بمبلغ واحد</div>
                        <div style="font-size:1.4rem;font-weight:800;color:#7b1fa2;margin-top:4px;">{{ number_format($resolution['total']) }} ج.م</div>
                    </div>
                </label>
                @endif

                {{-- Option 2: First Installment --}}
                @if(!$paidInst1)
                <label class="d-flex align-items-start gap-3 p-4 rounded-3"
                       style="background:#faf5ff;border:2px solid #ce93d8;cursor:pointer;transition:all 0.2s;"
                       id="label-inst1">
                    <input type="radio" name="choice" value="inst1"
                           class="form-check-input mt-1" style="width:20px;height:20px;accent-color:#7b1fa2;"
                           onchange="updateStyle()">
                    <div>
                        <div class="fw-bold" style="color:#4a148c;">القسط الأول</div>
                        <div class="text-muted" style="font-size:0.85rem;">يتبقى بعده قسط ثاني {{ number_format($resolution['inst2_amount']) }} ج.م</div>
                        <div style="font-size:1.4rem;font-weight:800;color:#7b1fa2;margin-top:4px;">{{ number_format($resolution['inst1_amount']) }} ج.م</div>
                    </div>
                </label>
                @endif

                {{-- Option 3: Second Installment --}}
                @if($paidInst1 && !$paidInst2)
                <label class="d-flex align-items-start gap-3 p-4 rounded-3"
                       style="background:#e8f5e9;border:2px solid #a5d6a7;cursor:pointer;"
                       id="label-inst2">
                    <input type="radio" name="choice" value="inst2"
                           class="form-check-input mt-1" style="width:20px;height:20px;accent-color:#2e7d32;"
                           onchange="updateStyle()">
                    <div>
                        <div class="fw-bold" style="color:#2e7d32;">القسط الثاني <span class="badge bg-success" style="font-size:0.7rem;">متاح الآن</span></div>
                        <div class="text-muted" style="font-size:0.85rem;">أتمم سداد بقية المصاريف الدراسية.</div>
                        <div style="font-size:1.4rem;font-weight:800;color:#2e7d32;margin-top:4px;">{{ number_format($resolution['inst2_amount']) }} ج.م</div>
                    </div>
                </label>
                @endif

                @if(!$paidInst1)
                <div class="d-flex align-items-start gap-3 p-4 rounded-3"
                     style="background:#f5f5f5;border:2px solid #e0e0e0;opacity:0.6;">
                    <div class="form-check-input mt-1" style="width:20px;height:20px;background:#ccc;border-radius:50%;border:2px solid #bbb;"></div>
                    <div>
                        <div class="fw-bold text-muted">القسط الثاني <span class="badge bg-secondary" style="font-size:0.7rem;">🔒 مقفل</span></div>
                        <div class="text-muted" style="font-size:0.85rem;">يجب سداد القسط الأول أولاً لفتح هذا الخيار.</div>
                        <div style="font-size:1.4rem;font-weight:800;color:#bbb;margin-top:4px;">{{ number_format($resolution['inst2_amount']) }} ج.م</div>
                    </div>
                </div>
                @endif
            </div>

            <div class="mb-4">
                <label class="form-label text-primary fw-bold">طريقة الدفع <span class="text-danger">*</span></label>
                <select name="payment_method" class="form-select" required>
                    <option value="Visa">فيزا / ماستركارد (Visa)</option>
                    <option value="Fawry">فوري (Fawry)</option>
                </select>
            </div>

            <button type="submit" class="btn-signin" style="background:#7b1fa2;margin-bottom:4px;">
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
function updateStyle() {
    const labels = document.querySelectorAll('[id^="label-"]');
    labels.forEach(lbl => {
        const radio = lbl.querySelector('input[type=radio]');
        if (radio && radio.checked) {
            lbl.style.borderWidth = '2.5px';
            lbl.style.boxShadow = '0 0 0 3px rgba(123,31,162,0.15)';
        } else {
            lbl.style.boxShadow = 'none';
        }
    });
}
</script>
@endsection
