@extends('layouts.app')
@section('title', 'تذاكر الدعم')
@section('page-heading', 'تذاكر الدعم')
@section('user-name', auth()->user()->name)

@section('content')
<div class="d-flex justify-content-between flex-wrap gap-2 mb-4">
    <p class="page-subtitle mb-0">رد على استفسارات الطلاب وتصنيف التذاكر</p>
    <form class="d-flex gap-2" method="GET">
        <select name="status" class="form-select form-select-sm">
            <option value="">كل الحالات</option>
            @foreach(['open','in_progress','resolved','closed'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ \App\Models\SupportTicket::statusLabel($s) }}</option>
            @endforeach
        </select>
        <select name="category" class="form-select form-select-sm">
            <option value="">كل التصنيفات</option>
            @foreach($categories as $k => $label)
                <option value="{{ $k }}" @selected(request('category') === $k)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-sm btn-primary">تصفية</button>
    </form>
</div>

<div class="table-responsive bg-white rounded shadow-sm">
    <table class="table table-hover mb-0">
        <thead class="table-light">
            <tr><th>#</th><th>الطالب</th><th>الموضوع</th><th>التصنيف</th><th>الحالة</th><th></th></tr>
        </thead>
        <tbody>
            @foreach($tickets as $t)
                <tr>
                    <td>{{ $t->id }}</td>
                    <td>{{ $t->student->name }}</td>
                    <td>{{ $t->subject }}</td>
                    <td>{{ $categories[$t->category] ?? $t->category }}</td>
                    <td>{{ \App\Models\SupportTicket::statusLabel($t->status) }}</td>
                    <td><a href="{{ route('staff.tickets.show', $t) }}" class="btn btn-sm btn-outline-primary">فتح</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
{{ $tickets->links() }}
@endsection
