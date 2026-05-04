@extends('layouts.app')

@section('title', 'تسجيل طالب جديد')
@section('page-heading', 'تسجيل طالب جديد')
@section('user-name', auth()->user()->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('affairs.student.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill mb-3">
        <i class="bi bi-arrow-right"></i> عودة للقائمة
    </a>
    <h2 class="page-title">تسجيل طالب جديد</h2>
    <p class="page-subtitle">أدخل بيانات الطالب لإضافته إلى نظام الجامعة</p>
</div>

<div class="row justify-content-center">
    <div class="col-xl-7 col-lg-9">
        <div class="stat-card">
            <form action="{{ route('affairs.student.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">الاسم الكامل <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="أدخل الاسم الكامل للطالب">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">الرقم القومي <span class="text-danger">*</span></label>
                        <input type="text" name="national_id" class="form-control text-start @error('national_id') is-invalid @enderror"
                               value="{{ old('national_id') }}" placeholder="14 رقم" dir="ltr" maxlength="14">
                        @error('national_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">الرقم المرجعي <span class="text-danger">*</span></label>
                        <input type="text" name="reference_number" class="form-control text-start @error('reference_number') is-invalid @enderror"
                               value="{{ old('reference_number') }}" placeholder="مثال: REF12345" dir="ltr">
                        @error('reference_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">الفرقة الدراسية <span class="text-danger">*</span></label>
                        <select name="academic_year" class="form-select @error('academic_year') is-invalid @enderror">
                            <option value="">-- اختر الفرقة --</option>
                            <option value="الفرقة الأولى" {{ old('academic_year') == 'الفرقة الأولى' ? 'selected' : '' }}>الفرقة الأولى</option>
                            <option value="الفرقة الثانية" {{ old('academic_year') == 'الفرقة الثانية' ? 'selected' : '' }}>الفرقة الثانية</option>
                            <option value="الفرقة الثالثة" {{ old('academic_year') == 'الفرقة الثالثة' ? 'selected' : '' }}>الفرقة الثالثة</option>
                            <option value="الفرقة الرابعة" {{ old('academic_year') == 'الفرقة الرابعة' ? 'selected' : '' }}>الفرقة الرابعة</option>
                            <option value="الفرقة الخامسة" {{ old('academic_year') == 'الفرقة الخامسة' ? 'selected' : '' }}>الفرقة الخامسة</option>
                        </select>
                        @error('academic_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">الكلية / البرنامج <span class="text-danger">*</span></label>
                        <input type="text" name="program" class="form-control @error('program') is-invalid @enderror"
                               value="{{ old('program') }}" placeholder="مثال: علوم الحاسب">
                        @error('program')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 mt-2">
                        <button type="submit" class="btn-primary-uni w-100" style="justify-content:center;padding:13px;">
                            <i class="bi bi-person-check-fill"></i> حفظ بيانات الطالب
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
