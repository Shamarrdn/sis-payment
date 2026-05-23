@extends('layouts.student')
@section('title', 'متابعة الطلبات')
@section('page-title', 'متابعة الطلبات')

@section('content')
<p class="text-muted mb-4">كل طلباتك في مكان واحد: مدفوعات، مستندات، بيانات، وتذاكر دعم</p>

<div class="table-responsive">
    <table class="table table-hover bg-white shadow-sm rounded overflow-hidden">
        <thead class="table-light">
            <tr>
                <th>النوع</th>
                <th>العنوان</th>
                <th>الحالة</th>
                <th>التاريخ</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $req)
                <tr>
                    <td>
                        @switch($req['type'])
                            @case('payment') <span class="badge bg-success">دفع</span> @break
                            @case('sensitive_data') <span class="badge bg-warning text-dark">بيانات</span> @break
                            @case('document') <span class="badge bg-info text-dark">مستند</span> @break
                            @case('ticket') <span class="badge bg-primary">دعم</span> @break
                        @endswitch
                    </td>
                    <td>{{ $req['title'] }}</td>
                    <td>{{ $req['status_label'] }}</td>
                    <td>{{ $req['date']->format('Y/m/d') }}</td>
                    <td><a href="{{ $req['url'] }}" class="btn btn-sm btn-outline-primary">عرض</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">لا توجد طلبات مسجلة</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
