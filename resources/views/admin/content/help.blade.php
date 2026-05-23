@extends('layouts.app')
@section('title', 'مركز المساعدة')
@section('page-heading', 'مركز المساعدة')
@section('user-name', auth()->user()->name)

@section('content')
<div class="d-flex justify-content-between mb-4">
    <p class="page-subtitle mb-0">مقالات إرشادية خطوة بخطوة للطلاب</p>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHelp">مقال جديد</button>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

@foreach($articles as $article)
    <div class="card border-0 shadow-sm mb-2">
        <div class="card-body d-flex justify-content-between">
            <div>
                <span class="badge bg-light text-dark">{{ $article->category }}</span>
                <h6 class="fw-bold mt-2">{{ $article->title }}</h6>
            </div>
            <div>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editHelp{{ $article->id }}">تعديل</button>
                <form action="{{ route('admin.help.destroy', $article) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف؟')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">حذف</button>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editHelp{{ $article->id }}">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('admin.help.update', $article) }}" method="POST" class="modal-content">
                @csrf @method('PUT')
                <div class="modal-header"><h5 class="modal-title">تعديل مقال</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">@include('admin.content._help_fields', ['article' => $article])</div>
                <div class="modal-footer"><button class="btn btn-primary">حفظ</button></div>
            </form>
        </div>
    </div>
@endforeach
{{ $articles->links() }}

<div class="modal fade" id="addHelp">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('admin.help.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">مقال جديد</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">@include('admin.content._help_fields', ['article' => null])</div>
            <div class="modal-footer"><button class="btn btn-primary">إضافة</button></div>
        </form>
    </div>
</div>
@endsection
