@extends('layouts.app')
@section('title', 'أكثر الخدمات')
@section('page-heading', 'أكثر الخدمات طلباً')
@section('user-name', auth()->user()->name)
@section('content')
<form class="row g-2 mb-4" method="GET">
    <div class="col-auto"><input type="date" name="date_from" value="{{ $from }}" class="form-control"></div>
    <div class="col-auto"><input type="date" name="date_to" value="{{ $to }}" class="form-control"></div>
    <div class="col-auto"><button class="btn btn-primary">تصفية</button></div>
</form>
<table class="table bg-white shadow-sm"><thead><tr><th>الخدمة</th><th>عدد الطلبات</th><th>الإيراد</th></tr></thead>
<tbody>@foreach($rows as $r)<tr><td>{{ $r->service?->name ?? '—' }}</td><td>{{ $r->requests }}</td><td>{{ number_format($r->revenue) }} ج.م</td></tr>@endforeach</tbody></table>
@endsection
