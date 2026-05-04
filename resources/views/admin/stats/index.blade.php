@extends('layouts.app')
@section('title', 'الإحصائيات والتقارير المالية')
@section('page-heading', 'الإحصائيات والتقارير')
@section('user-name', auth()->user()->name)
@section('user-name', auth()->user()->name)

@section('content')
<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 text-center" style="background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: white;">
            <div class="small fw-bold opacity-75 text-uppercase">إجمالي الإيرادات</div>
            <div class="display-6 fw-bold my-2">{{ number_format($global['total_revenue']) }} <small style="font-size:1rem;">ج.م</small></div>
            <div class="small opacity-75">{{ number_format($global['total_payments']) }} عملية ناجحة</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
            <div class="small fw-bold text-muted text-uppercase">إجمالي الطلاب المسجلين</div>
            <div class="display-6 fw-bold my-2 text-dark">{{ number_format($global['total_students']) }}</div>
            <div class="small text-muted">عبر كل الكليات والأقسام</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
            <div class="small fw-bold text-muted text-uppercase">عمليات قيد المراجعة / طلبات استرداد</div>
            <div class="display-6 fw-bold my-2 text-warning">{{ $global['pending_payments'] }} / {{ $global['refund_requests'] }}</div>
            <div class="small text-muted">تحتاج لاتخاذ إجراء إداري</div>
        </div>
    </div>
</div>

{{-- Unassigned payments notice --}}
@if(($global['unassigned_payments'] ?? 0) > 0)
<div class="alert alert-warning border-0 rounded-4 mb-4 d-flex align-items-center gap-3">
    <i class="bi bi-exclamation-triangle-fill fs-4"></i>
    <div>
        <strong>{{ number_format($global['unassigned_payments']) }}</strong> مدفوعة
        بإجمالي <strong>{{ number_format($global['unassigned_revenue']) }} ج.م</strong>
        غير مرتبطة بكلية — يُنصح بتعيين الكليات للطلاب لتحسين دقة التقارير.
    </div>
</div>
@endif

<h4 class="fw-bold mb-4 pb-2 border-bottom"><i class="bi bi-building me-2"></i> تحليل الأداء حسب الكلية</h4>

<div class="row g-4">
    @foreach($byFaculty as $item)
    @php
        $f = $item['faculty'];
        $totalGlobal = $global['total_revenue'] ?: 1;
        $pct = round(($item['revenue'] / $totalGlobal) * 100, 1);
        $tuitionPct = $item['revenue'] > 0 ? round(($item['tuition_revenue'] / $item['revenue']) * 100) : 0;
    @endphp
    <div class="col-xl-4 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-header border-0 py-3 px-4 d-flex justify-content-between align-items-center" style="background: #f8fafc;">
                <div class="fw-bold text-primary">{{ $f->name }}</div>
                <span class="badge bg-white text-primary border">{{ $f->code }}</span>
            </div>
            <div class="card-body p-4">
                {{-- Main stats --}}
                <div class="row text-center g-3 mb-3">
                    <div class="col-6">
                        <div class="small text-muted">إجمالي الإيرادات</div>
                        <div class="fw-bold text-dark fs-5">{{ number_format($item['revenue']) }} <small>ج.م</small></div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted">الطلاب</div>
                        <div class="fw-bold text-dark fs-5">{{ number_format($item['students']) }}</div>
                    </div>
                </div>

                {{-- Revenue breakdown: tuition vs other --}}
                <div class="mb-3 p-3 rounded-3" style="background:#f8fafc;">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">مصاريف دراسية</span>
                        <span class="fw-bold text-primary">{{ number_format($item['tuition_revenue']) }} ج.م</span>
                    </div>
                    <div class="progress mb-2" style="height:6px;">
                        <div class="progress-bar bg-primary" style="width:{{ $tuitionPct }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">خدمات أخرى</span>
                        <span class="fw-bold text-success">{{ number_format($item['other_revenue']) }} ج.م</span>
                    </div>
                    <div class="progress" style="height:6px;">
                        <div class="progress-bar bg-success" style="width:{{ 100 - $tuitionPct }}%"></div>
                    </div>
                </div>

                {{-- Payments & pending --}}
                <div class="d-flex justify-content-between small mb-3">
                    <span><i class="bi bi-check-circle-fill text-success me-1"></i>{{ number_format($item['payments']) }} عملية ناجحة</span>
                    @if($item['pending'] > 0)
                        <span class="badge bg-warning text-dark">{{ $item['pending'] }} معلقة</span>
                    @endif
                </div>

                {{-- Share of total --}}
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">نسبة من إجمالي الإيرادات</span>
                        <span class="fw-bold">{{ $pct }}%</span>
                    </div>
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar" style="width:{{ $pct }}%; background:linear-gradient(90deg,#1a2d5a,#3b82f6);"></div>
                    </div>
                </div>

                {{-- Top service --}}
                <div class="p-2 rounded-3 mb-4" style="background:#f1f5f9;">
                    <div class="small text-muted mb-1">الخدمة الأكثر طلباً</div>
                    <div class="fw-bold text-truncate small"><i class="bi bi-star-fill text-warning me-1"></i> {{ $item['top_service'] ?? 'لا يوجد بيانات' }}</div>
                </div>

                <div class="d-grid">
                    <a href="{{ route('admin.statistics.faculty', $f) }}" class="btn btn-outline-primary btn-sm rounded-pill">
                        <i class="bi bi-eye me-1"></i> عرض تفاصيل الكلية الكاملة
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
