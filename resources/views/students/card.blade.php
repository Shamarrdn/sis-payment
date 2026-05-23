@extends('layouts.app')
@section('title', 'كارت الطالب')
@section('page-heading', 'كارت الطالب')
@section('user-name', auth()->user()->name ?? '')

@section('content')
<div class="d-flex gap-2 mb-4">
    <a href="{{ route('affairs.student.show', $student) }}" class="btn btn-sm btn-outline-secondary">← الملف</a>
    <a href="{{ route('affairs.student.print', $student) }}" target="_blank" class="btn btn-sm btn-primary"><i class="bi bi-printer"></i> طباعة PDF</a>
    <a href="{{ route('affairs.student.qr', $student) }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-qr-code"></i> QR</a>
</div>

<div class="card border-0 shadow-lg mx-auto" style="max-width:420px;border-radius:20px;overflow:hidden;">
    <div class="p-4 text-center text-white" style="background:linear-gradient(135deg,#1a2d5a,#233872);">
        <img src="{{ asset('images/uni.jpg') }}" width="72" height="72" class="rounded-circle border border-3 border-warning mb-2" alt="">
        <h4 class="fw-bold mb-0">{{ $student->name }}</h4>
        <div class="opacity-75 small">{{ $student->reference_number }}</div>
    </div>
    <div class="card-body p-4">
        <div class="row g-3 small">
            <div class="col-6"><span class="text-muted d-block">الرقم القومي</span><strong>{{ $student->national_id }}</strong></div>
            <div class="col-6"><span class="text-muted d-block">الحالة</span><strong>{{ $student->status ?? 'نشط' }}</strong></div>
            <div class="col-12"><span class="text-muted d-block">الكلية</span><strong>{{ $student->facultyName() }}</strong></div>
            <div class="col-12"><span class="text-muted d-block">القسم / البرنامج</span><strong>{{ $student->departmentName() }}</strong></div>
            <div class="col-6"><span class="text-muted d-block">الفرقة</span><strong>{{ $student->academic_year ?: '—' }}</strong></div>
            <div class="col-6"><span class="text-muted d-block">الهاتف</span><strong>{{ $student->phone ?: '—' }}</strong></div>
        </div>
    </div>
</div>
@endsection
