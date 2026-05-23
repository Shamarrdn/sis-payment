@extends('layouts.student')
@section('title', 'تذاكر الدعم')
@section('page-title', 'تذاكر الدعم')

@section('content')
<div class="d-flex justify-content-between mb-4">
    <p class="text-muted mb-0">افتح تذكرة عند وجود مشكلة في البيانات أو الخدمة أو الحساب</p>
    <a href="{{ route('student.tickets.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> تذكرة جديدة</a>
</div>

@forelse($tickets as $ticket)
    <div class="card border-0 shadow-sm mb-2">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-1">{{ $ticket->subject }}</h6>
                <small class="text-muted">
                    {{ \App\Support\TicketCategories::label($ticket->category) }}
                    — {{ \App\Models\SupportTicket::statusLabel($ticket->status) }}
                    — {{ $ticket->created_at->format('Y/m/d') }}
                </small>
            </div>
            <a href="{{ route('student.tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary">فتح</a>
        </div>
    </div>
@empty
    <div class="text-center py-5 text-muted">لا توجد تذاكر — يمكنك فتح تذكرة جديدة</div>
@endforelse

{{ $tickets->links() }}
@endsection
