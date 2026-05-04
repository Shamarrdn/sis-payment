@extends('layouts.app')

@section('title', 'لوحة تحكم المدير')
@section('page-heading', 'لوحة تحكم المدير المسئول')
@section('user-name', auth()->user()->name)

@section('user-name', auth()->user()->name)

@section('content')
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h2 class="page-title">لوحة التحكم الإدارية</h2>
            <p class="page-subtitle mb-0">نظرة عامة على أداء المنظومة والمدفوعات الحالية</p>
        </div>
        <div class="text-muted small">
            <i class="bi bi-clock-history me-1"></i>
            آخر تحديث: {{ now()->format('H:i') }}
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4 d-flex align-items-center" style="border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: #065f46;">
            <i class="bi bi-check-circle-fill me-3 fs-4"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <div class="row g-4 mb-5">
        <div class="col-xl-2 col-md-4">
            <div class="stat-card">
                <div class="icon-wrap" style="background: rgba(15, 23, 42, 0.05); color: #0f172a;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="small text-muted fw-bold text-uppercase mb-1">إجمالي الطلاب</div>
                <div class="h3 fw-800 mb-0" style="color: #0f172a;">{{ number_format($stats['total_students']) }}</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="stat-card">
                <div class="icon-wrap" style="background: rgba(16, 185, 129, 0.08); color: #10b981;">
                    <i class="bi bi-credit-card-fill"></i>
                </div>
                <div class="small text-muted fw-bold text-uppercase mb-1">عمليات ناجحة</div>
                <div class="h3 fw-800 mb-0" style="color: #065f46;">{{ number_format($stats['total_payments']) }}</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="stat-card">
                <div class="icon-wrap" style="background: rgba(212, 175, 55, 0.1); color: #d4af37;">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="small text-muted fw-bold text-uppercase mb-1">إجمالي الإيرادات</div>
                <div class="h4 fw-800 mb-0" style="color: #854d0e;">{{ number_format($stats['total_revenue']) }} <small>ج.م</small></div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="stat-card">
                <div class="icon-wrap" style="background: rgba(59, 130, 246, 0.08); color: #3b82f6;">
                    <i class="bi bi-calendar-day"></i>
                </div>
                <div class="small text-muted fw-bold text-uppercase mb-1">عمليات اليوم</div>
                <div class="h3 fw-800 mb-0" style="color: #1e40af;">{{ number_format($stats['today_payments']) }}</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <a href="{{ route('admin.review.pending') }}" class="text-decoration-none">
                <div class="stat-card" style="border-color: #f59e0b;">
                    <div class="icon-wrap" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="small text-muted fw-bold text-uppercase mb-1">معلقة / Pending</div>
                    <div class="h3 fw-800 mb-0" style="color: #b45309;">{{ number_format($stats['pending_payments']) }}</div>
                </div>
            </a>
        </div>
        <div class="col-xl-2 col-md-4">
            <a href="{{ route('admin.refunds.index') }}" class="text-decoration-none">
                <div class="stat-card" style="border-color: #ef4444;">
                    <div class="icon-wrap" style="background: rgba(239, 68, 68, 0.08); color: #ef4444;">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </div>
                    <div class="small text-muted fw-bold text-uppercase mb-1">طلبات استرداد</div>
                    <div class="h3 fw-800 mb-0" style="color: #b91c1c;">{{ number_format($stats['refund_requests']) }}</div>
                </div>
            </a>
        </div>
    </div>

    {{-- Faculty Breakdown Cards --}}
    <h6 class="fw-bold mb-3 d-flex align-items-center"><i class="bi bi-building me-2"></i> توزيع الكليات المفعّلة</h6>
    <div class="row g-3 mb-5">
        @foreach($facultyStats as $f)
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="border-right: 4px solid #1a2d5a!important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold text-primary">{{ $f['name'] }}</div>
                            <div class="h5 fw-bold mb-1">{{ number_format($f['revenue']) }} <small class="text-muted small">ج.م</small></div>
                            <div class="small text-muted">{{ number_format($f['students']) }} طالب</div>
                        </div>
                        <div class="badge bg-light text-primary fs-6">{{ $f['code'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filter Form + Export --}}
    <div class="card mb-4 border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-funnel me-2"></i>تصفية وبحث</h6>
                @if(auth()->user()->hasPermission('export_data'))
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.export.payments', request()->query()) }}" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> تصدير CSV
                    </a>
                    <a href="{{ route('admin.export.daily', ['date' => today()->toDateString()]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-calendar-check me-1"></i> تسوية اليوم
                    </a>
                </div>
                @endif
            </div>
            <form action="{{ route('admin.dashboard') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">بحث (اسم / رقم قومي / مرجع)</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="ابحث هنا..."></div>
                <div class="col-md-2">
                    <label class="form-label">البرنامج</label>
                    <input type="text" name="program" class="form-control" value="{{ request('program') }}" placeholder="اسم البرنامج">
                </div>
                <div class="col-md-2">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-select">
                        <option value="">الكل</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>ناجحة / Paid</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلقة / Pending</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>فشلت / Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">فئة المستخدم</label>
                    <select name="user_category" class="form-select">
                        <option value="">الكل</option>
                        <option value="Student" {{ request('user_category') == 'Student' ? 'selected' : '' }}>طالب</option>
                        <option value="Graduate" {{ request('user_category') == 'Graduate' ? 'selected' : '' }}>خريج</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الكلية</label>
                    <select name="faculty_id" class="form-select">
                        <option value="">الكل</option>
                        @foreach($faculties as $fac)
                            <option value="{{ $fac->id }}" {{ request('faculty_id') == $fac->id ? 'selected' : '' }}>{{ $fac->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter"></i> تصفية</button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-light"><i class="bi bi-arrow-repeat"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="themed-table">
        <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-bold"><i class="bi bi-receipt me-2"></i> سجل المدفوعات والمعاملات</h5>
            <span class="text-muted small">{{ $payments->total() }} نتيجة</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">الطالب</th>
                        <th>الرقم القومي</th>
                        <th>البرنامج / القطاع</th>
                        <th>الخدمة</th>
                        <th>المبلغ</th>
                        <th>الكمية</th>
                        <th>الإجمالي</th>
                        <th>طريقة الدفع</th>
                        <th>التاريخ</th>
                        <th>الحالة</th>
                        <th class="pe-4">الرقم المرجعي</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td class="ps-4 mt-2">
                                <div class="fw-bold">{{ optional($payment->student)->name ?? 'غير معروف' }}</div>
                                <div class="small text-muted">{{ $payment->user_category }}</div>
                            </td>
                            <td>{{ optional($payment->student)->national_id }}</td>
                            <td>{{ $payment->faculty_snapshot }} - {{ $payment->department_snapshot }}</td>
                            <!-- <td></td> -->
                            <td>
                                {{ optional($payment->service)->name }}
                                @if($payment->notes) <div class="small text-muted">{{ $payment->notes }}</div> @endif
                            </td>
                            <td>{{ number_format($payment->amount) }}</td>
                            <td>{{ $payment->quantity }}</td>
                            <td class="fw-bold text-success">{{ number_format($payment->total_amount) }}</td>
                            <td>{{ $payment->payment_method }}</td>
                            <td class="text-muted small">{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d H:i') }}</td>
                            <td>
                                @if($payment->status === 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($payment->status === 'failed')
                                    <span class="badge bg-danger">Failed</span>
                                @elseif($payment->status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @else
                                    <span class="badge bg-secondary">{{ $payment->status }}</span>
                                @endif
                            </td>
                            <td class="pe-4 font-monospace small text-muted">{{ $payment->reference_number }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="text-center p-4">لا توجد بيانات متاحة.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{ $payments->links() }}
        </div>
    </div>
@endsection
