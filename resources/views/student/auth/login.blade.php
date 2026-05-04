@extends('layouts.auth')

@section('title', 'تسجيل دخول الطلاب')
@section('hero-subtitle', 'بوابة الطلاب للدفع الإلكتروني')

@section('content')
<div class="auth-card">
    <div class="card-header-uni">
        <i class="bi bi-mortarboard-fill" style="font-size:1.4rem;color:var(--accent)"></i>
        تسجيل دخول الطلاب
    </div>
    <div class="card-body">
        <p class="text-muted mb-4 text-center">أدخل رقمك القومي ورقمك المرجعي للدخول</p>

        @if($errors->any())
            <div class="alert alert-danger rounded-3 mb-3">
                @foreach($errors->all() as $error)
                    <div><i class="bi bi-exclamation-circle-fill me-2"></i>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="/student/login">
            @csrf
            <div class="mb-3">
                <label class="form-label">الرقم القومي</label>
                <input type="text" class="form-control text-start" dir="ltr" name="national_id"
                       value="{{ old('national_id') }}" placeholder="أدخل رقمك القومي (14 رقم)" maxlength="14" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label">الرقم المرجعي <small class="text-muted">(كلمة المرور)</small></label>
                <input type="text" class="form-control text-start" dir="ltr" name="reference_number"
                       placeholder="أدخل رقمك المرجعي" required>
            </div>
            <button type="submit" class="btn-signin">
                <i class="bi bi-box-arrow-in-left me-1"></i> دخول البوابة
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="{{ url('/') }}" class="text-muted text-decoration-none" style="font-size:0.9rem;">
                <i class="bi bi-arrow-right me-1"></i> العودة للرئيسية
            </a>
        </div>
    </div>
</div>
@endsection
