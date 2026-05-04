@extends('layouts.auth')

@section('title', 'سجل المدفوعات')
@section('hero-subtitle', 'الاطلاع على الحركات المالية السابقة')

@section('content')
<div class="auth-card" style="max-width: 800px;">
    <div class="card-header-uni">
        <i class="bi bi-clock-history" style="font-size:1.4rem;color:var(--accent)"></i>
        سجل المدفوعات الخاصة بك
    </div>
    
    <div class="card-body p-4">
        @if(session('error'))
            <div class="alert alert-danger mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if($payments->isEmpty())
            <div class="text-center p-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                لا يوجد أي حركات دفع سابقة.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>التاريخ</th>
                            <th>الخدمة / المادة</th>
                            <th>المبلغ الإجمالي</th>
                            <th>وسيلة الدفع</th>
                            <th>الحالة</th>
                            <th>الرقم المرجعي</th>
                            <th>الإيصال</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                        <tr>
                            <td class="text-muted" style="font-size: 0.9rem;">{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d H:i') }}</td>
                            <td>
                                <strong>{{ $payment->service->name }}</strong>
                                @if($payment->notes)
                                    <div class="text-muted" style="font-size: 0.8rem;">{{ $payment->notes }}</div>
                                @endif
                            </td>
                            <td class="fw-bold">{{ number_format($payment->total_amount) }} ج.م</td>
                            <td>{{ $payment->payment_method ?? 'غير محدد' }}</td>
                            <td>
                                @if($payment->status === 'paid')
                                    <span class="badge bg-success">ناجحة</span>
                                @elseif($payment->status === 'failed')
                                    <span class="badge bg-danger">فشلت</span>
                                @elseif($payment->status === 'cancelled')
                                    <span class="badge bg-secondary">ملغية</span>
                                @else
                                    <span class="badge bg-warning text-dark">قيد الانتظار</span>
                                @endif
                            </td>
                            <td class="text-muted font-monospace" style="font-size: 0.85rem;">{{ $payment->reference_number }}</td>
                            <td>
                                @if($payment->status === 'paid')
                                    <a href="{{ route('student.receipt', $payment) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-printer"></i> الإيصال
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 d-flex justify-content-center">
                {{ $payments->links() }}
            </div>
        @endif

        <div class="text-center mt-4 border-top pt-4">
            <a href="{{ route('student.dashboard') }}" class="btn btn-light px-4">
                <i class="bi bi-arrow-right me-1"></i> العودة للرئيسية
            </a>
        </div>
    </div>
</div>
@endsection
