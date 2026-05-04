@extends('layouts.app')
@section('title', 'إحصائيات كلية ' . $faculty->name)
@section('page-heading', 'تحليل أداء كلية ' . $faculty->name)
@section('user-name', auth()->user()->name)
@section('sidebar-title', 'نظام SIS')
@section('sidebar-subtitle', 'مدير النظام')

@section('sidebar-nav')
    <span class="nav-label">الأساسية</span>
    <a href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> نظرة عامة</a>
    <a href="{{ route('admin.statistics.index') }}" class="active"><i class="bi bi-bar-chart-line-fill"></i> إحصائيات متقدمة</a>
    <span class="nav-label">البنية التنظيمية</span>
    <a href="{{ route('admin.faculties.index') }}"><i class="bi bi-building"></i> الكليات والأقسام</a>
    <a href="{{ route('admin.tuition.index') }}"><i class="bi bi-cash-stack"></i> إعدادات الرسوم</a>
    <span class="nav-label">الإدارة</span>
    <a href="{{ route('admin.employees.index') }}"><i class="bi bi-people-fill"></i> إدارة الموظفين</a>
    <a href="{{ route('admin.services.index') }}"><i class="bi bi-gear-fill"></i> الخدمات والأسعار</a>
    <span class="nav-label">المراجعة المالية</span>
    <a href="{{ route('admin.refunds.index') }}"><i class="bi bi-arrow-counterclockwise"></i> طلبات الاسترداد</a>
    <a href="{{ route('admin.review.pending') }}"><i class="bi bi-hourglass-split"></i> العمليات المعلقة</a>
    <a href="{{ route('admin.audit.index') }}"><i class="bi bi-journal-text"></i> سجل المراجعة</a>
    <span class="nav-label">الإعدادات</span>
    <a href="{{ route('admin.settings.index') }}"><i class="bi bi-sliders"></i> الإعدادات المالية</a>
@endsection
@section('sidebar-logout')
    <form action="{{ route('logout') }}" method="POST">@csrf<button type="submit"><i class="bi bi-box-arrow-right"></i> تسجيل خروج</button></form>
@endsection

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.statistics.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-right me-1"></i> العودة للإحصائيات العامة</a>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-4" style="border-right: 4px solid #3b82f6!important;">
            <div class="small text-muted fw-bold">إيرادات الكلية</div>
            <div class="h3 fw-bold my-1 text-primary">{{ number_format($stats['revenue']) }} <small>ج.م</small></div>
            <div class="small text-muted">{{ number_format($stats['payments']) }} عملية ناجحة</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-4" style="border-right: 4px solid #10b981!important;">
            <div class="small text-muted fw-bold">إجمالي الطلاب</div>
            <div class="h3 fw-bold my-1 text-success">{{ number_format($stats['students']) }}</div>
            <div class="small text-muted">في مختلف الفرق والأقسام</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-4" style="border-right: 4px solid #f59e0b!important;">
            <div class="small text-muted fw-bold">عمليات معلقة</div>
            <div class="h3 fw-bold my-1 text-warning">{{ number_format($stats['pending']) }}</div>
            <div class="small text-muted">بانتظار المراجعة</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-4" style="border-right: 4px solid #ef4444!important;">
            <div class="small text-muted fw-bold">طلبات استرداد</div>
            <div class="h3 fw-bold my-1 text-danger">{{ number_format($stats['refunds']) }}</div>
            <div class="small text-muted">قيد الطلب للموافقة</div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Breakdown by Department --}}
    <div class="col-md-7">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 py-3 px-4 fw-bold"><i class="bi bi-diagram-3 me-2"></i> توزيع الإيرادات حسب الأقسام</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-4">القسم</th>
                            <th>عدد الطلاب</th>
                            <th>عدد العمليات</th>
                            <th class="pe-4 text-end">الإيرادات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($byDept as $d)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold">{{ $d['dept']->name }}</span>
                                <div class="small text-muted font-monospace">{{ $d['dept']->code }}</div>
                            </td>
                            <td>{{ number_format($d['students']) }}</td>
                            <td>{{ number_format($d['payments']) }}</td>
                            <td class="pe-4 text-end fw-bold text-success">{{ number_format($d['revenue']) }} <small>ج.م</small></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Breakdown by Academic Year --}}
    <div class="col-md-5">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 py-3 px-4 fw-bold"><i class="bi bi-layers me-2"></i> توزيع الإيرادات حسب الفرقة الدراسية</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-4">الفرقة / المستوى</th>
                            <th>عدد العمليات</th>
                            <th class="pe-4 text-end">الإيرادات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($byYear as $y)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $y->academic_year ?? 'غير محدد' }}</td>
                            <td>{{ number_format($y->cnt) }}</td>
                            <td class="pe-4 text-end fw-bold text-primary">{{ number_format($y->revenue) }} <small>ج.م</small></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
