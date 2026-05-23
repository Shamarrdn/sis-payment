@extends('layouts.auth')
@section('title', 'تغيير كلمة المرور')
@section('hero-subtitle', 'يجب تعيين كلمة مرور جديدة للمتابعة')

@section('content')
<div class="auth-card">
    @if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif
    <form method="POST" action="{{ ($guard ?? 'web') === 'student' ? route('student.password.update') : route('employee.password.update') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-bold">كلمة المرور الجديدة</label>
            <input type="password" name="password" class="form-control" required minlength="8">
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">تأكيد كلمة المرور</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">حفظ والمتابعة</button>
    </form>
</div>
@endsection
