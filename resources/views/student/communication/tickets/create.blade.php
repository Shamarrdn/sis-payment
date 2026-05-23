@extends('layouts.student')
@section('title', 'تذكرة دعم جديدة')
@section('page-title', 'تذكرة دعم جديدة')

@section('content')
<div class="card border-0 shadow-sm" style="max-width:640px;">
    <div class="card-body p-4">
        <form action="{{ route('student.tickets.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-bold">الموضوع</label>
                <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">التصنيف</label>
                <select name="category" class="form-select" required>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">الأولوية</label>
                <select name="priority" class="form-select">
                    <option value="low">منخفضة</option>
                    <option value="medium" selected>متوسطة</option>
                    <option value="high">عالية</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">وصف المشكلة</label>
                <textarea name="message" class="form-control" rows="5" required>{{ old('message') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">إرسال التذكرة</button>
            <a href="{{ route('student.tickets.index') }}" class="btn btn-link">إلغاء</a>
        </form>
    </div>
</div>
@endsection
