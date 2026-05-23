@extends('layouts.app')
@section('title', 'ملخص شهري')
@section('page-heading', 'ملخص شهري')
@section('user-name', auth()->user()->name)
@section('content')
<form class="mb-4" method="GET"><input type="month" name="month" value="{{ $month }}" class="form-control w-auto d-inline"><button class="btn btn-primary">عرض</button></form>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card p-3 border-0 shadow-sm"><small>عمليات مدفوعة</small><div class="h4">{{ $stats['paid_count'] }}</div></div></div>
    <div class="col-md-3"><div class="card p-3 border-0 shadow-sm"><small>إيرادات</small><div class="h4">{{ number_format($stats['revenue']) }} ج.م</div></div></div>
    <div class="col-md-3"><div class="card p-3 border-0 shadow-sm"><small>طلاب دفعوا</small><div class="h4">{{ $stats['students_paid'] }}</div></div></div>
    <div class="col-md-3"><div class="card p-3 border-0 shadow-sm"><small>طلبات مكتملة</small><div class="h4">{{ $stats['completed'] }}</div></div></div>
    <div class="col-md-3"><div class="card p-3 border-0 shadow-sm"><small>إجمالي الطلاب</small><div class="h4">{{ $stats['total_students'] }}</div></div></div>
</div>
<h5 class="fw-bold">أكثر الخدمات (خلال الشهر)</h5>
<table class="table bg-white shadow-sm"><thead><tr><th>الخدمة</th><th>العدد</th></tr></thead>
<tbody>@foreach($topServices as $r)<tr><td>{{ $r->service?->name }}</td><td>{{ $r->total }}</td></tr>@endforeach</tbody></table>
@endsection
