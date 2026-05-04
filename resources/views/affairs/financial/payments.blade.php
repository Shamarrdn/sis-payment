@extends('layouts.app')

@section('title', 'تقارير المدفوعات - الشئون المالية')
@section('page-heading', 'تقارير المدفوعات')
@section('user-name', auth()->user()->name)

@section('content')
<div class="mb-4">
    <h2 class="page-title">تقارير سجل المدفوعات</h2>
    <p class="page-subtitle">بحث وفلترة سجلات المدفوعات حسب التاريخ والنوع</p>
</div>

{{-- Filters --}}
<div class="stat-card mb-4">
    <form method="GET" action="{{ route('affairs.financial.payments') }}" class="row g-3 align-items-end">
        <div class="col-md-2">
            <label class="form-label">من تاريخ</label>
            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">إلى تاريخ</label>
            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">نوع الخدمة</label>
            <select name="service_type" class="form-select">
                <option value="">جميع الأنواع</option>
                @foreach($serviceTypes as $type)
                    <option value="{{ $type }}" {{ request('service_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">الكلية</label>
            <select name="faculty_id" class="form-select">
                <option value="">كل الكليات</option>
                @foreach($faculties as $fac)
                    <option value="{{ $fac->id }}" {{ request('faculty_id') == $fac->id ? 'selected' : '' }}>{{ $fac->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">بحث (اسم طالب)</label>
            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="ابحث بالاسم...">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn-primary-uni flex-grow-1" style="justify-content:center;">
                <i class="bi bi-filter"></i> تصفية
            </button>
            <a href="{{ route('affairs.financial.payments') }}" class="btn btn-outline-secondary rounded-pill" title="إعادة ضبط">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </form>
</div>

{{-- Summary Badge --}}
@if($payments->total() > 0)
    <div class="d-flex align-items-center gap-3 mb-3">
        <span class="badge rounded-pill px-3 py-2" style="background:#eff3fb;color:#1a2d5a;font-size:0.9rem;">
            <i class="bi bi-list-check"></i> {{ $payments->total() }} نتيجة
        </span>
        <span class="badge rounded-pill px-3 py-2" style="background:#e8f5e9;color:#2e7d32;font-size:0.9rem;">
            إجمالي: {{ number_format($payments->sum('amount'), 2) }} ج.م
        </span>
    </div>
@endif

<div class="themed-table">
    <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th>تاريخ الدفع</th>
                <th>اسم الطالب</th>
                <th>الرقم القومي</th>
                <th>الكلية / الفرقة</th>
                <th>الخدمة (النوع)</th>
                <th>تفاصيل</th>
                <th>رقم الإيصال</th>
                <th>المبلغ (ج.م)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td class="text-muted">{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</td>
                    <td><strong>{{ $payment->student->name }}</strong></td>
                    <td><code class="text-muted">{{ $payment->student->national_id }}</code></td>
                    <td>{{ $payment->student->program }} <small class="text-muted d-block">{{ $payment->student->academic_year }}</small></td>
                    <td>
                        {{ $payment->service->name }}
                        <br><small class="badge bg-light text-dark border mt-1">{{ $payment->service->type }}</small>
                    </td>
                    <td>
                        @if($payment->notes)
                            <span class="badge rounded-pill" style="background:#fff3cd;color:#856404;font-size:0.8rem;">{{ $payment->notes }}</span>
                        @else <span class="text-muted">—</span> @endif
                    </td>
                    <td><code class="text-primary fw-bold">{{ $payment->reference_number }}</code></td>
                    <td><strong class="text-success">{{ number_format($payment->amount, 2) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-search display-5 d-block mb-2 text-secondary"></i>
                        لا توجد مدفوعات تطابق معايير البحث.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $payments->appends(request()->query())->links() }}
</div>
@endsection
