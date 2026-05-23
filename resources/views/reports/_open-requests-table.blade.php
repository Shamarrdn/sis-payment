<h5 class="fw-bold mb-3">{{ $title ?? 'الطلبات' }}</h5>
<table class="table table-hover bg-white shadow-sm">
    <thead class="table-light"><tr><th>الطالب</th><th>الخدمة</th><th>الحالة</th><th>منذ</th><th></th></tr></thead>
    <tbody>
        @forelse($payments as $p)
            <tr class="{{ !empty($showDelayed) && \App\Services\PaymentRequestService::isDelayed($p) ? 'table-warning' : '' }}">
                <td>{{ $p->student?->name }}</td>
                <td>{{ $p->service?->name }}</td>
                <td>{{ \App\Services\PaymentRequestService::fulfillmentLabel($p->fulfillment_status) }}</td>
                <td>{{ $p->payment_date?->diffForHumans() }}</td>
                <td>
                    @if($p->fulfillment_status === 'awaiting_processing')
                        <form action="{{ route('fulfillment.start', $p) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-primary">بدء</button></form>
                    @elseif($p->fulfillment_status === 'in_progress')
                        <form action="{{ route('fulfillment.complete', $p) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-success">إكمال</button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted">لا توجد طلبات</td></tr>
        @endforelse
    </tbody>
</table>
