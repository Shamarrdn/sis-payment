@extends('layouts.app')
@section('title', 'آخر العمليات')
@section('page-heading', 'آخر العمليات في النظام')
@section('user-name', auth()->user()->name)
@section('content')
<div class="row g-4">
    <div class="col-lg-7">
        <h6 class="fw-bold">سجل النشاط الإداري</h6>
        @foreach($logs as $log)
            <div class="card border-0 shadow-sm mb-2 p-3">
                <strong>{{ $log->action }}</strong>
                <div class="small text-muted">{{ $log->user?->name }} — {{ $log->created_at->diffForHumans() }}</div>
            </div>
        @endforeach
    </div>
    <div class="col-lg-5">
        <h6 class="fw-bold">تسجيلات الدخول</h6>
        @foreach($logins as $l)
            <div class="card border-0 shadow-sm mb-2 p-3">
                <strong>{{ $l->guard === 'student' ? ($l->student?->name ?? 'طالب') : ($l->user?->name ?? 'موظف') }}</strong>
                <div class="small {{ $l->success ? 'text-success' : 'text-danger' }}">{{ $l->success ? 'نجاح' : 'فشل' }} — {{ $l->created_at->format('Y/m/d H:i') }}</div>
            </div>
        @endforeach
    </div>
</div>
@endsection
