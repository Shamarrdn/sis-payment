@extends('layouts.auth')

@section('title', 'سجل المدفوعات والإيصالات')
@section('hero-subtitle', 'سجل رسمي يوثق كافة المعاملات المالية والإيصالات الخاصة بك')

@section('content')
<div class="auth-card" style="max-width: 1000px; padding: 0;">
    <div class="card-header-uni d-flex align-items-center gap-2" style="background: var(--primary); padding: 20px;">
        <i class="bi bi-folder2-open" style="font-size:1.6rem;color:var(--accent)"></i>
        سجل المدفوعات والإيصالات
    </div>
    
    <div class="card-body p-4 bg-light">
        @if(session('error'))
            <div class="alert alert-danger mb-4 rounded-3 shadow-sm">
                <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('error') }}
            </div>
        @endif

        @if($payments->isEmpty())
            <div class="text-center p-5 text-muted bg-white rounded-4 shadow-sm border">
                <i class="bi bi-inbox fs-1 d-block mb-3 text-secondary"></i>
                <h5 class="fw-bold text-dark">الأرشيف فارغ</h5>
                <p class="mb-0">لا يوجد أي إيصالات محفوظة في الأرشيف الرقمي حالياً.</p>
            </div>
        @else
            <div class="row g-4">
                @foreach($payments as $payment)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; background: #fff; border: 1px solid #e2e8f0 !important; transition: transform 0.2s;">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted font-monospace" style="font-size: 0.85rem;"><i class="bi bi-hash me-1"></i>{{ $payment->reference_number }}</span>
                                @if($payment->status === 'paid')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3 py-1" style="font-size: 0.75rem;"><i class="bi bi-check-circle-fill me-1"></i> مكتمل</span>
                                @elseif($payment->status === 'failed')
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-3 py-1" style="font-size: 0.75rem;"><i class="bi bi-x-circle-fill me-1"></i> ملغية</span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-dark border border-warning rounded-pill px-3 py-1" style="font-size: 0.75rem;"><i class="bi bi-hourglass-split me-1"></i> معلقة</span>
                                @endif
                            </div>
                            <h6 class="fw-bold mb-2" style="color: var(--primary); font-size: 1.05rem; line-height: 1.4;">{{ $payment->service->name }}</h6>
                            @if($payment->notes)
                                <div class="text-muted mb-2" style="font-size: 0.8rem;">{{ $payment->notes }}</div>
                            @endif
                            <div class="text-muted small mb-4" style="font-size: 0.8rem;">
                                <i class="bi bi-calendar-event me-1"></i> {{ \Carbon\Carbon::parse($payment->payment_date)->format('Y/m/d - h:i A') }}
                            </div>
                            
                            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small text-muted mb-1" style="font-size: 0.75rem; font-weight: 600;">القيمة المسددة</div>
                                    <div class="fw-bold text-dark fs-5">{{ number_format($payment->total_amount) }} <span class="fs-6 text-muted">ج.م</span></div>
                                </div>
                                <div>
                                    @if($payment->status === 'paid')
                                        <a href="{{ route('student.receipt', $payment) }}" class="btn btn-outline-primary rounded-pill px-3 btn-sm" style="font-weight: 600;">
                                            <i class="bi bi-printer me-1"></i> طباعة
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div class="mt-4 d-flex justify-content-center">
                {{ $payments->links() }}
            </div>
        @endif

        <div class="text-center mt-5">
            <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary px-4 rounded-pill">
                <i class="bi bi-arrow-right me-1"></i> العودة للرئيسية
            </a>
        </div>
    </div>
</div>
@endsection
