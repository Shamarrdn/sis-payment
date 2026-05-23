@extends('layouts.app')
@section('title', 'الإشعارات')
@section('page-heading', 'إشعارات الموظف')
@section('user-name', auth()->user()->name)

@section('content')
<div class="d-flex justify-content-between mb-4">
    <p class="page-subtitle mb-0">طلبات جديدة وتذاكر دعم</p>
    @if(auth()->user()->unreadNotifications->count())
        <form action="{{ route('staff.notifications.read-all') }}" method="POST">@csrf
            <button class="btn btn-sm btn-outline-primary">تعليم الكل كمقروء</button>
        </form>
    @endif
</div>

@forelse($notifications as $n)
    <div class="card border-0 shadow-sm mb-2 {{ $n->read_at ? '' : 'border-start border-warning border-4' }}">
        <div class="card-body d-flex justify-content-between">
            <div>
                <h6 class="fw-bold">{{ $n->data['title'] ?? 'إشعار' }}</h6>
                <p class="text-muted small mb-0">{{ $n->data['message'] ?? '' }}</p>
                <small class="text-muted">{{ $n->created_at->diffForHumans() }}</small>
            </div>
            @if(!$n->read_at)
                <a href="{{ route('staff.notifications.read', $n->id) }}" class="btn btn-sm btn-primary">عرض</a>
            @endif
        </div>
    </div>
@empty
    <p class="text-muted text-center py-5">لا توجد إشعارات</p>
@endforelse

{{ $notifications->links() }}
@endsection
