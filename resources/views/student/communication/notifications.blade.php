@extends('layouts.student')
@section('title', 'الإشعارات')
@section('page-title', 'الإشعارات')

@section('content')
<div class="d-flex justify-content-between mb-4">
    <p class="text-muted mb-0">تنبيهات الدفع، الطلبات، والإعلانات</p>
    @if(auth()->guard('student')->user()->unreadNotifications->count())
        <form action="{{ route('student.notifications.read-all') }}" method="POST">
            @csrf
            <button class="btn btn-sm btn-outline-primary">تعليم الكل كمقروء</button>
        </form>
    @endif
</div>

@forelse($notifications as $n)
    <div class="card border-0 shadow-sm mb-2 {{ $n->read_at ? '' : 'border-start border-primary border-4' }}">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-1">{{ $n->data['title'] ?? 'إشعار' }}</h6>
                <p class="mb-1 text-muted small">{{ $n->data['message'] ?? '' }}</p>
                <small class="text-muted">{{ $n->created_at->diffForHumans() }}</small>
            </div>
            @if(!$n->read_at)
                <a href="{{ route('student.notifications.read', $n->id) }}" class="btn btn-sm btn-primary">عرض</a>
            @endif
        </div>
    </div>
@empty
    <div class="text-center py-5 text-muted">لا توجد إشعارات</div>
@endforelse

{{ $notifications->links() }}
@endsection
