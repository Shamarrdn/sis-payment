@extends('layouts.student')
@section('title', 'الأسئلة الشائعة')
@section('page-title', 'الأسئلة الشائعة')

@section('content')
<p class="text-muted mb-4">إجابات عن الخدمات، المصروفات، واستخدام النظام</p>

@forelse($faqs as $category => $items)
    <h5 class="fw-bold text-primary mb-3">{{ $category }}</h5>
    <div class="accordion mb-4" id="faq-{{ Str::slug($category) }}">
        @foreach($items as $faq)
            <div class="accordion-item border-0 shadow-sm mb-2">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-{{ $faq->id }}">
                        {{ $faq->question }}
                    </button>
                </h2>
                <div id="faq-{{ $faq->id }}" class="accordion-collapse collapse" data-bs-parent="#faq-{{ Str::slug($category) }}">
                    <div class="accordion-body">{{ $faq->answer }}</div>
                </div>
            </div>
        @endforeach
    </div>
@empty
    <div class="text-center py-5 text-muted">لم تُضف أسئلة شائعة بعد</div>
@endforelse
@endsection
