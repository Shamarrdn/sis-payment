@extends('layouts.app')

@section('title', 'سجل العمليات الإدارية - Audit Log')
@section('page-heading', 'سجل العمليات الإدارية')
@section('user-name', auth()->user()->name)

@section('user-name', auth()->user()->name)

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="page-title">سجل العمليات الإدارية</h2>
        <p class="page-subtitle mb-0">كل تعديل حصل في النظام — مين، إيه، وإمتى.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
@endif

{{-- Filters --}}
<div class="card mb-4 border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <form action="{{ route('admin.audit.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">نوع العملية</label>
                <input type="text" name="action" class="form-control" value="{{ request('action') }}" placeholder="مثال: Update Service">
            </div>
            <div class="col-md-3">
                <label class="form-label">الموظف</label>
                <select name="user_id" class="form-select">
                    <option value="">الكل</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('user_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">من</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">إلى</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">نوع البيانات</label>
                <select name="model_type" class="form-select">
                    <option value="">الكل</option>
                    @foreach($modelTypes ?? [] as $mt)
                        <option value="{{ class_basename($mt) }}" @selected(request('model_type') === class_basename($mt))>{{ class_basename($mt) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="themed-table">
    <div class="p-4 border-bottom">
        <h5 class="mb-0 fw-bold"><i class="bi bi-journal-text me-2"></i>سجل التعديلات والعمليات</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">#</th>
                    <th>الموظف المسئول</th>
                    <th>العملية</th>
                    <th>السجل / الكيان</th>
                    <th>البيانات قبل التعديل</th>
                    <th>البيانات بعد التعديل</th>
                    <th class="pe-4">التاريخ والوقت</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="ps-4 text-muted small">{{ $log->id }}</td>
                    <td>
                        @if($log->user)
                            <div class="fw-bold">{{ $log->user->name }}</div>
                            <div class="small text-muted">{{ $log->user->role }}</div>
                        @else
                            <span class="text-muted small">النظام تلقائياً</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge rounded-pill px-3 py-2
                            {{ str_contains($log->action, 'Delete') || str_contains($log->action, 'Cancel') ? 'bg-danger' :
                               (str_contains($log->action, 'Create') ? 'bg-success' :
                               (str_contains($log->action, 'Refund') || str_contains($log->action, 'Reversal') ? 'bg-warning text-dark' : 'bg-primary')) }}">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td class="text-muted small">
                        @if($log->model_type)
                            {{ class_basename($log->model_type) }} #{{ $log->model_id }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if($log->old_values)
                            <div class="small text-muted font-monospace" style="max-width:200px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">
                                {{ json_encode($log->old_values, JSON_UNESCAPED_UNICODE) }}
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($log->new_values)
                            <div class="small text-success font-monospace" style="max-width:200px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">
                                {{ json_encode($log->new_values, JSON_UNESCAPED_UNICODE) }}
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="pe-4 text-muted small">
                        {{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center p-5 text-muted">
                        <i class="bi bi-journal-x fs-1 d-block mb-3"></i>
                        لا توجد عمليات مسجلة حتى الآن.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 border-top">{{ $logs->links() }}</div>
</div>
@endsection
