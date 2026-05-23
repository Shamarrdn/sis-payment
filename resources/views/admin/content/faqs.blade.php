@extends('layouts.app')
@section('title', 'الأسئلة الشائعة')
@section('page-heading', 'الأسئلة الشائعة')
@section('user-name', auth()->user()->name)

@section('content')
<div class="d-flex justify-content-between mb-4">
    <p class="page-subtitle mb-0">إدارة محتوى صفحة الأسئلة الشائعة للطلاب</p>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFaq">إضافة سؤال</button>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

@foreach($faqs as $faq)
    <div class="card border-0 shadow-sm mb-2">
        <div class="card-body d-flex justify-content-between">
            <div>
                <span class="badge bg-light text-dark">{{ $faq->category }}</span>
                @if(!$faq->is_active)<span class="badge bg-secondary">معطل</span>@endif
                <h6 class="fw-bold mt-2">{{ $faq->question }}</h6>
                <p class="text-muted small mb-0">{{ Str::limit($faq->answer, 120) }}</p>
            </div>
            <div>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editFaq{{ $faq->id }}">تعديل</button>
                <form action="{{ route('admin.faqs.destroy', $faq) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف؟')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">حذف</button>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editFaq{{ $faq->id }}">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('admin.faqs.update', $faq) }}" method="POST" class="modal-content">
                @csrf @method('PUT')
                <div class="modal-header"><h5 class="modal-title">تعديل سؤال</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    @include('admin.content._faq_fields', ['faq' => $faq])
                </div>
                <div class="modal-footer"><button class="btn btn-primary">حفظ</button></div>
            </form>
        </div>
    </div>
@endforeach
{{ $faqs->links() }}

<div class="modal fade" id="addFaq">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('admin.faqs.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">سؤال جديد</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">@include('admin.content._faq_fields', ['faq' => null])</div>
            <div class="modal-footer"><button class="btn btn-primary">إضافة</button></div>
        </form>
    </div>
</div>
@endsection
