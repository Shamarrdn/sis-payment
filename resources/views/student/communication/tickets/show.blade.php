@extends('layouts.student')
@section('title', $ticket->subject)
@section('page-title', 'تذكرة #' . $ticket->id)

@section('content')
<div class="mb-3">
    <span class="badge bg-secondary">{{ \App\Support\TicketCategories::label($ticket->category) }}</span>
    <span class="badge bg-primary">{{ \App\Models\SupportTicket::statusLabel($ticket->status) }}</span>
</div>

<div class="card border-0 shadow-sm mb-4" style="max-height:400px;overflow-y:auto;">
    <div class="card-body">
        @foreach($ticket->replies as $reply)
            <div class="mb-3 p-3 rounded {{ $reply->user_id ? 'bg-light' : 'bg-primary bg-opacity-10' }}">
                <div class="small fw-bold mb-1">
                    {{ $reply->user_id ? ($reply->user?->name ?? 'موظف') : 'أنت' }}
                    <span class="text-muted fw-normal">— {{ $reply->created_at->format('Y/m/d H:i') }}</span>
                </div>
                <div style="white-space:pre-wrap;">{{ $reply->message }}</div>
            </div>
        @endforeach
    </div>
</div>

@if(!in_array($ticket->status, ['closed', 'resolved']))
    <form action="{{ route('student.tickets.reply', $ticket) }}" method="POST" class="card border-0 shadow-sm">
        <div class="card-body">
            @csrf
            <label class="form-label fw-bold">ردك</label>
            <textarea name="message" class="form-control mb-3" rows="3" required></textarea>
            <button type="submit" class="btn btn-primary">إرسال</button>
        </div>
    </form>
@else
    <div class="alert alert-secondary">تم إغلاق هذه التذكرة.</div>
@endif

<a href="{{ route('student.tickets.index') }}" class="btn btn-link mt-3">← العودة للتذاكر</a>
@endsection
