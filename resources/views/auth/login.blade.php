@extends('layouts.auth')

@section('title', 'تسجيل دخول الموظفين')
@section('hero-subtitle', 'بوابة الموظفين')

@section('content')
<div class="auth-card">
    <div class="card-header-uni">
        <i class="bi bi-person-badge-fill" style="font-size:1.4rem;color:var(--accent)"></i>
        تسجيل دخول الموظفين
    </div>
    <div class="card-body">
        <p class="text-muted mb-4 text-center">أدخل بيانات حسابك للدخول إلى لوحة التحكم</p>

        @if($errors->any())
            <div class="alert alert-danger rounded-3 mb-3">
                @foreach($errors->all() as $error)
                    <div><i class="bi bi-exclamation-circle-fill me-2"></i>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" class="form-control text-start" dir="ltr" name="email"
                       value="{{ old('email') }}" placeholder="example@uni.edu" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label">كلمة المرور</label>
                <input type="password" class="form-control text-start" dir="ltr" name="password"
                       placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-signin">
                <i class="bi bi-box-arrow-in-left me-1"></i> دخول النظام
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
