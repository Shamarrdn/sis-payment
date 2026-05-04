@extends('layouts.app')

@section('title', 'التسوية اليومية - الشئون المالية')
@section('page-heading', 'التسوية اليومية')
@section('user-name', auth()->user()->name)

@section('content')
<div class="mb-4">
    <h2 class="page-title">التسوية اليومية</h2>
    <p class="page-subtitle">ملخص المدفوعات ليوم {{ now()->format('Y-m-d') }}</p>
</div>

{{-- Stats Row --}}
<div class="row g-4 mb-5">
    <div class="col-xl-4">
        <div class="stat-card">
            <div class="icon-wrap" style="background:#e8f5e9;color:#2e7d32;"><i class="bi bi-cash-stack"></i></div>
            <p class="text-muted mb-1" style="font-size:0.85rem;">إجمالي التحصيل اليومي</p>
            <h2 style="font-size:2rem;font-weight:800;color:#2e7d32;">{{ number_format($todayTotal, 2) }} <small style="font-size:0.9rem;">ج.م</small></h2>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="stat-card">
            <div class="icon-wrap" style="background:#eff3fb;color:#1a2d5a;"><i class="bi bi-receipt-cutoff"></i></div>
            <p class="text-muted mb-1" style="font-size:0.85rem;">عدد المعاملات اليوم</p>
            <h2 style="font-size:2rem;font-weight:800;color:#1a2d5a;">{{ $todayCount }}</h2>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="stat-card">
            <div class="icon-wrap" style="background:#fff3e0;color:#e65100;"><i class="bi bi-calendar-check-fill"></i></div>
            <p class="text-muted mb-1" style="font-size:0.85rem;">إجمالي الشهر الحالي</p>
            <h2 style="font-size:2rem;font-weight:800;color:#e65100;">{{ number_format($monthTotal, 2) }} <small style="font-size:0.9rem;">ج.م</small></h2>
        </div>
    </div>
</div>

{{-- Today's Payments --}}
<div class="mb-3 d-flex justify-content-between align-items-center">
    <h5 style="font-weight:700;color:#1a2d5a;">آخر المعاملات اليوم</h5>
    <a href="{{ route('affairs.financial.payments') }}" class="btn btn-sm btn-outline-primary rounded-pill">
        عرض التقرير الكامل <i class="bi bi-arrow-left"></i>
    </a>
</div>

<div class="themed-table">
    <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th>رقم الإيصال</th>
                <th>اسم الطالب</th>
                <th>الخدمة</th>
                <th>تفاصيل</th>
                <th>المبلغ</th>
                <th>التوقيت</th>
            </tr>
        </thead>
        <tbody>
            @forelse($todayPayments as $payment)
                <tr>
                    <td><code class="text-primary fw-bold">{{ $payment->reference_number }}</code></td>
                    <td>{{ $payment->student->name }}</td>
                    <td>
                        {{ $payment->service->name }}
                        <br><small class="badge bg-light text-dark border">{{ $payment->service->type }}</small>
                    </td>
                    <td>
                        @if($payment->notes)
                            <span class="badge rounded-pill" style="background:#fff3cd;color:#856404;">{{ $payment->notes }}</span>
                        @else <span class="text-muted">—</span> @endif
                    </td>
                    <td><strong class="text-success">{{ number_format($payment->amount, 2) }} ج.م</strong></td>
                    <td class="text-muted">{{ \Carbon\Carbon::parse($payment->payment_date)->format('H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="bi bi-inbox display-5 d-block mb-2 text-secondary"></i>
                        لا توجد معاملات اليوم حتى الآن.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
