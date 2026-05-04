@extends('layouts.app')

@section('title', 'إيصالات الطالب - ' . $student->name)
@section('sidebar-title', 'شئون الطلاب')
@section('sidebar-subtitle', 'لوحة إدارة الطلاب')

@section('sidebar-nav')
    <span class="nav-label">القائمة الرئيسية</span>
    <a href="{{ route('affairs.student.index') }}">
        <i class="bi bi-people-fill"></i> إدارة الطلاب
    </a>
    <a href="{{ route('affairs.student.create') }}">
        <i class="bi bi-person-plus-fill"></i> تسجيل طالب جديد
    </a>
@endsection

@section('sidebar-logout')
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit"><i class="bi bi-box-arrow-right"></i> تسجيل خروج</button>
    </form>
@endsection

@section('page-heading', 'إيصالات الطالب')
@section('user-name', auth()->user()->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('affairs.student.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill mb-3">
        <i class="bi bi-arrow-right"></i> عودة للقائمة
    </a>
    <h2 class="page-title">إيصالات الدفع</h2>
    <p class="page-subtitle">سجل المدفوعات للطالب: <strong>{{ $student->name }}</strong> | ر.ق: {{ $student->national_id }}</p>
</div>

{{-- Student Info Card --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="icon-wrap mx-auto" style="background:#eff3fb;color:#1a2d5a;"><i class="bi bi-person-badge"></i></div>
            <div class="text-muted mb-1" style="font-size:0.8rem;">الفرقة / الكلية</div>
            <strong>{{ $student->academic_year }}</strong><br>
            <small class="text-muted">{{ $student->program }}</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="icon-wrap mx-auto" style="background:#e8f5e9;color:#2e7d32;"><i class="bi bi-cash-stack"></i></div>
            <div class="text-muted mb-1" style="font-size:0.8rem;">إجمالي المدفوعات</div>
            <strong style="font-size:1.3rem;color:#2e7d32;">{{ number_format($payments->sum('amount'), 2) }} ج.م</strong>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="icon-wrap mx-auto" style="background:#fff3e0;color:#e65100;"><i class="bi bi-receipt-cutoff"></i></div>
            <div class="text-muted mb-1" style="font-size:0.8rem;">عدد العمليات</div>
            <strong style="font-size:1.3rem;">{{ $payments->count() }}</strong>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="icon-wrap mx-auto" style="background:#fce4ec;color:#c62828;"><i class="bi bi-key-fill"></i></div>
            <div class="text-muted mb-1" style="font-size:0.8rem;">الرقم المرجعي</div>
            <strong style="font-size:0.95rem;">{{ $student->reference_number }}</strong>
        </div>
    </div>
</div>

<div class="themed-table">
    <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th>رقم الإيصال</th>
                <th>الخدمة / النوع</th>
                <th>تفاصيل</th>
                <th>المبلغ</th>
                <th>السنة الدراسية</th>
                <th>تاريخ الدفع</th>
                <th>الحالة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td><code class="text-primary fw-bold">{{ $payment->reference_number }}</code></td>
                    <td>
                        <strong>{{ $payment->service->name }}</strong>
                        <br><small class="text-muted badge bg-light text-dark border">{{ $payment->service->type }}</small>
                    </td>
                    <td>
                        @if($payment->notes)
                            <span class="badge rounded-pill" style="background:#fff3cd;color:#856404;">{{ $payment->notes }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td><strong class="text-success">{{ number_format($payment->amount, 2) }} ج.م</strong></td>
                    <td>{{ $payment->academic_year }}</td>
                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d H:i') }}</td>
                    <td>
                        @if($payment->status === 'completed')
                            <span class="badge bg-success rounded-pill">مكتمل</span>
                        @else
                            <span class="badge bg-warning text-dark rounded-pill">{{ $payment->status }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-inbox display-5 d-block mb-2 text-secondary"></i>
                        لا توجد عمليات دفع مسجلة لهذا الطالب.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
