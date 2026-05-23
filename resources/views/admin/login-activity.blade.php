@extends('layouts.app')
@section('title', 'نشاط تسجيل الدخول')
@section('page-heading', 'نشاط تسجيل الدخول')
@section('user-name', auth()->user()->name)
@section('content')
<form method="GET" class="row g-2 mb-4 card p-3 border-0 shadow-sm">
    <div class="col-md-2"><select name="guard" class="form-select"><option value="">الكل</option><option value="web" @selected(request('guard')==='web')>موظف</option><option value="student" @selected(request('guard')==='student')>طالب</option></select></div>
    <div class="col-md-2"><select name="success" class="form-select"><option value="">الكل</option><option value="1" @selected(request('success')==='1')>نجاح</option><option value="0" @selected(request('success')==='0')>فشل</option></select></div>
    <div class="col-md-2"><input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control"></div>
    <div class="col-md-2"><input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control"></div>
    <div class="col-md-2"><button class="btn btn-primary w-100">تصفية</button></div>
</form>
<table class="table bg-white shadow-sm"><thead><tr><th>النوع</th><th>المستخدم</th><th>المعرّف</th><th>IP</th><th>النتيجة</th><th>الوقت</th></tr></thead>
<tbody>
@foreach($activities as $a)
<tr>
    <td>{{ $a->guard === 'student' ? 'طالب' : 'موظف' }}</td>
    <td>{{ $a->student?->name ?? $a->user?->name ?? '—' }}</td>
    <td>{{ $a->email_or_id }}</td>
    <td class="small">{{ $a->ip_address }}</td>
    <td><span class="badge bg-{{ $a->success ? 'success' : 'danger' }}">{{ $a->success ? 'نجاح' : 'فشل' }}</span></td>
    <td>{{ $a->created_at->format('Y/m/d H:i') }}</td>
</tr>
@endforeach
</tbody></table>
{{ $activities->links() }}
@endsection
