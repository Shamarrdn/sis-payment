@extends('layouts.app')

@section('title', 'العمليات المعلقة - Pending Review')
@section('page-heading', 'مراجعة العمليات المعلقة')
@section('user-name', auth()->user()->name)

@section('user-name', auth()->user()->name)

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="page-title">العمليات المعلقة (Pending)</h2>
        <p class="page-subtitle mb-0">مراجعة يدوية للعمليات التي لم تكتمل تلقائياً.</p>
    </div>
    <span class="badge bg-warning text-dark fs-6 px-3 py-2">{{ $payments->total() }} عملية معلقة</span>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger mb-4">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<div class="themed-table">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">الطالب</th>
                    <th>الخدمة</th>
                    <th>المبلغ</th>
                    <th>وسيلة الدفع</th>
                    <th>رقم المرجع</th>
                    <th>التاريخ</th>
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
                    <td>{{ $payment->payment_method }}</td>
                    <td class="font-monospace small text-muted">{{ $payment->reference_number }}</td>
                    <td class="small text-muted">{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d H:i') }}</td>
                    <td class="pe-4">
                        <div class="d-flex gap-2">
                            {{-- Mark as Paid --}}
                            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#markPaidModal{{ $payment->id }}">
                                <i class="bi bi-check-circle"></i> تأكيد يدوي
                            </button>
                            {{-- Cancel --}}
                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal{{ $payment->id }}">
                                <i class="bi bi-x-circle"></i> إلغاء
                            </button>
                        </div>

                        {{-- Mark Paid Modal --}}
                        <div class="modal fade" id="markPaidModal{{ $payment->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="{{ route('admin.review.mark-paid', $payment) }}" method="POST" class="modal-content">
                                    @csrf
                                    <div class="modal-header border-0">
                                        <h5 class="modal-title fw-bold">تأكيد الدفع يدوياً</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="alert alert-info mb-4">
                                            سيتم تأكيد دفع <strong>{{ optional($payment->service)->name }}</strong> للطالب <strong>{{ optional($payment->student)->name }}</strong>.<br>
                                            هذا الإجراء سيُسجَّل في سجل المراجعة الإدارية.
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">رقم المرجع (اختياري التعديل)</label>
                                            <input type="text" name="reference_number" class="form-control" value="{{ $payment->reference_number }}">
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">إلغاء</button>
                                        <button type="submit" class="btn btn-success px-5">تأكيد الدفع</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Cancel Modal --}}
                        <div class="modal fade" id="cancelModal{{ $payment->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="{{ route('admin.payments.cancel', $payment) }}" method="POST" class="modal-content">
                                    @csrf
                                    <div class="modal-header border-0">
                                        <h5 class="modal-title fw-bold">إلغاء العملية</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">سبب الإلغاء <span class="text-danger">*</span></label>
                                            <textarea name="cancel_reason" class="form-control" rows="3" required placeholder="يرجى توضيح سبب إلغاء هذه العملية..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">رجوع</button>
                                        <button type="submit" class="btn btn-danger px-5">تأكيد الإلغاء</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center p-5 text-muted">
                        <i class="bi bi-check2-all fs-1 d-block mb-3 text-success"></i>
                        لا توجد عمليات معلقة حالياً. 🎉
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 border-top">{{ $payments->links() }}</div>
</div>
@endsection
