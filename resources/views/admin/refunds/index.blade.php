@extends('layouts.app')

@section('title', 'طلبات الاسترداد')
@section('page-heading', 'إدارة طلبات الاسترداد (Refunds)')
@section('user-name', auth()->user()->name)

@section('user-name', auth()->user()->name)

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="page-title">طلبات الاسترداد والإلغاء</h2>
        <p class="page-subtitle mb-0">مراجعة واعتماد أو رفض طلبات الاسترداد المالي.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger mb-4">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<div class="themed-table">
    <div class="p-4 border-bottom">
        <h5 class="mb-0 fw-bold"><i class="bi bi-arrow-counterclockwise me-2"></i>طلبات الاسترداد الحالية</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">الطالب</th>
                    <th>الخدمة</th>
                    <th>المبلغ</th>
                    <th>تاريخ الدفع</th>
                    <th>سبب طلب الاسترداد</th>
                    <th>حالة الاسترداد</th>
                    <th class="pe-4">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td class="ps-4">
                        <div class="fw-bold">{{ optional($payment->student)->name }}</div>
                        <div class="small text-muted">{{ optional($payment->student)->national_id }}</div>
                    </td>
                    <td>{{ optional($payment->service)->name }}</td>
                    <td class="fw-bold">{{ number_format($payment->total_amount) }} ج.م</td>
                    <td class="text-muted small">{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</td>
                    <td class="text-muted small" style="max-width:200px;">{{ $payment->refund_reason }}</td>
                    <td>
                        @if($payment->refund_status === 'requested')
                            <span class="badge bg-warning text-dark">بانتظار الاعتماد</span>
                        @elseif($payment->refund_status === 'approved')
                            <span class="badge bg-info text-dark">معتمد / بانتظار التنفيذ</span>
                        @elseif($payment->refund_status === 'refunded')
                            <span class="badge bg-success">تم الاسترداد</span>
                        @else
                            <span class="badge bg-secondary">{{ $payment->refund_status }}</span>
                        @endif
                    </td>
                    <td class="pe-4">
                        <div class="d-flex gap-2 flex-wrap">
                            @if($payment->refund_status === 'requested')
                                <form action="{{ route('admin.refunds.approve', $payment) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-info" onclick="return confirm('اعتماد طلب الاسترداد؟')">
                                        <i class="bi bi-check2"></i> اعتماد
                                    </button>
                                </form>
                            @endif
                            @if(in_array($payment->refund_status, ['requested', 'approved']))
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#processRefund{{ $payment->id }}">
                                    <i class="bi bi-cash-coin"></i> تنفيذ الاسترداد
                                </button>
                            @endif
                        </div>

                        {{-- Process Refund Modal --}}
                        <div class="modal fade" id="processRefund{{ $payment->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="{{ route('admin.refunds.process', $payment) }}" method="POST" class="modal-content">
                                    @csrf
                                    <div class="modal-header border-0">
                                        <h5 class="modal-title fw-bold">تنفيذ استرداد: {{ optional($payment->service)->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="alert alert-warning mb-4">
                                            سيتم استرداد مبلغ <strong>{{ number_format($payment->total_amount) }} ج.م</strong> للطالب {{ optional($payment->student)->name }}.<br>
                                            هذه العملية لا يمكن التراجع عنها.
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">سبب الاسترداد <span class="text-danger">*</span></label>
                                            <textarea name="refund_reason" class="form-control" rows="3" required>{{ $payment->refund_reason }}</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">إلغاء</button>
                                        <button type="submit" class="btn btn-danger px-5">تأكيد الاسترداد</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center p-5 text-muted">
                        <i class="bi bi-check-circle fs-1 d-block mb-3 text-success"></i>
                        لا توجد طلبات استرداد حالياً.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 border-top">{{ $payments->links() }}</div>
</div>
@endsection
