@extends('layouts.app')
@section('title', 'تذكرة #' . $ticket->id)
@section('page-heading', 'تذكرة دعم #' . $ticket->id)
@section('user-name', auth()->user()->name)

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="mb-3">
    <strong>{{ $ticket->student->name }}</strong> — {{ $categories[$ticket->category] ?? $ticket->category }}
    — {{ $ticket->subject }}
</div>

<div class="card border-0 shadow-sm mb-4" style="max-height:360px;overflow-y:auto;">
    <div class="card-body">
        @foreach($ticket->replies as $reply)
            <div class="mb-3 p-3 rounded {{ $reply->user_id ? 'bg-primary bg-opacity-10' : 'bg-light' }}">
                <div class="small fw-bold">{{ $reply->user_id ? ($reply->user?->name ?? 'موظف') : $ticket->student->name }}</div>
                <div style="white-space:pre-wrap;">{{ $reply->message }}</div>
                <small class="text-muted">{{ $reply->created_at->format('Y/m/d H:i') }}</small>
            </div>
        @endforeach
    </div>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <form action="{{ route('staff.tickets.reply', $ticket) }}" method="POST" class="card border-0 shadow-sm">
            @csrf
            <div class="card-body">
                <label class="form-label fw-bold">رد الموظف</label>
                <textarea name="message" class="form-control mb-3" rows="4" required></textarea>
                <select name="status" class="form-select mb-3">
                    <option value="">— بدون تغيير الحالة —</option>
                    @foreach(['in_progress','resolved','closed'] as $s)
                        <option value="{{ $s }}">{{ \App\Models\SupportTicket::statusLabel($s) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary">إرسال الرد</button>
            </div>
        </form>
    </div>
    <div class="col-md-4">
        <form action="{{ route('staff.tickets.status', $ticket) }}" method="POST" class="card border-0 shadow-sm">
            @csrf @method('PATCH')
            <div class="card-body">
                <label class="form-label fw-bold">تحديث الحالة</label>
                <select name="status" class="form-select mb-3" required>
                    @foreach(['open','in_progress','resolved','closed'] as $s)
                        <option value="{{ $s }}" @selected($ticket->status === $s)>{{ \App\Models\SupportTicket::statusLabel($s) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-outline-secondary w-100">تحديث</button>
            </div>
        </form>
    </div>
</div>

<a href="{{ route('staff.tickets.index') }}" class="btn btn-link mt-3">← العودة</a>
@endsection
