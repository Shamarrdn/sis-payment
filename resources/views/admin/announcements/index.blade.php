@extends('layouts.app')
@section('title', 'الإعلانات')
@section('page-heading', 'إدارة الإعلانات')
@section('user-name', auth()->user()->name)

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="page-title">الإعلانات</h2>
        <p class="page-subtitle mb-0">إعلانات عامة أو مستهدفة (كلية / قسم / فرقة)</p>
    </div>
    <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> إعلان جديد</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="table-responsive bg-white rounded shadow-sm">
    <table class="table table-hover mb-0">
        <thead class="table-light">
            <tr>
                <th>العنوان</th>
                <th>الاستهداف</th>
                <th>الحالة</th>
                <th>التاريخ</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($announcements as $a)
                <tr>
                    <td>{{ $a->title }}</td>
                    <td class="small">
                        @if($a->isGeneral()) عام @else
                            {{ $a->faculty?->name ?? '—' }}
                            / {{ $a->department?->name ?? '—' }}
                            / {{ $a->academic_year ?? '—' }}
                        @endif
                    </td>
                    <td>
                        @if($a->is_published)<span class="badge bg-success">منشور</span>@else<span class="badge bg-secondary">مسودة</span>@endif
                    </td>
                    <td>{{ $a->created_at->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ route('admin.announcements.edit', $a) }}" class="btn btn-sm btn-outline-primary">تعديل</a>
                        <form action="{{ route('admin.announcements.destroy', $a) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف الإعلان؟')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">حذف</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
{{ $announcements->links() }}
@endsection
