@extends('layouts.student')
@section('title', 'الإعلانات')
@section('page-title', 'الإعلانات')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">إعلانات عامة ومستهدفة حسب كليتك وفرقتك</p>
</div>

@forelse($announcements as $item)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 class="fw-bold mb-0">{{ $item->title }}</h5>
                @if($item->isGeneral())
                    <span class="badge bg-primary">عام</span>
                @else
                    <span class="badge bg-info text-dark">مستهدف</span>
                @endif
            </div>
            <p class="mb-2" style="white-space:pre-wrap;">{{ $item->content }}</p>
            <small class="text-muted">
                <i class="bi bi-clock"></i> {{ $item->created_at->format('Y/m/d H:i') }}
                @if($item->expires_at)
                    — ينتهي {{ $item->expires_at->format('Y/m/d') }}
                @endif
            </small>
        </div>
    </div>
@empty
    <div class="text-center py-5 text-muted">
        <i class="bi bi-megaphone fs-1 d-block mb-3"></i>
        لا توجد إعلانات حالياً
    </div>
@endforelse

{{ $announcements->links() }}
@endsection
