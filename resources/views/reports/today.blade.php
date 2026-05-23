@extends('layouts.app')
@section('title', 'ملخص اليوم')
@section('page-heading', 'ملخص اليوم')
@section('user-name', auth()->user()->name)
@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card p-4 border-0 shadow-sm"><div class="text-muted">طلبات جديدة اليوم</div><div class="h2 fw-bold">{{ $newToday }}</div></div></div>
    <div class="col-md-4"><div class="card p-4 border-0 shadow-sm"><div class="text-muted">منتهية اليوم</div><div class="h2 fw-bold text-success">{{ $completedToday }}</div></div></div>
    <div class="col-md-4"><div class="card p-4 border-0 shadow-sm"><div class="text-muted">متأخرة</div><div class="h2 fw-bold text-danger">{{ $delayed->count() }}</div></div></div>
</div>
@include('reports._open-requests-table', ['payments' => $allOpen, 'title' => 'طلبات مفتوحة'])
@endsection
