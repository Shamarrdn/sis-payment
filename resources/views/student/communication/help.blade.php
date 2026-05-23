@extends('layouts.student')
@section('title', 'مركز المساعدة')
@section('page-title', 'مركز المساعدة')

@section('content')
<p class="text-muted mb-4">شرح خطوات الخدمات والعمليات الأساسية</p>

@forelse($articles as $category => $items)
    <h5 class="fw-bold text-primary mb-3"><i class="bi bi-folder2-open me-2"></i>{{ $category }}</h5>
    @foreach($items as $article)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold">{{ $article->title }}</h6>
                <div class="text-muted" style="white-space:pre-wrap;">{{ $article->content }}</div>
            </div>
        </div>
    @endforeach
@empty
    <div class="text-center py-5 text-muted">لم تُضف مقالات مساعدة بعد</div>
@endforelse
@endsection
