@extends('layouts.app')
@section('title', 'إدارة الكليات والأقسام')
@section('page-heading', 'هيكل الجامعة — الكليات والأقسام')
@section('user-name', auth()->user()->name)
@section('user-name', auth()->user()->name)

@section('content')
@if(session('success'))
    <div class="alert alert-success mb-4"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger mb-4">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="page-title mb-1">الكليات والأقسام</h2>
        <p class="page-subtitle mb-0">قم بإدارة البنية التنظيمية للجامعة — الكليات والبرامج والأقسام</p>
    </div>
    <button class="btn-primary-uni" data-bs-toggle="modal" data-bs-target="#addFacultyModal">
        <i class="bi bi-plus-lg me-1"></i> إضافة كلية
    </button>
</div>

<div class="row g-4">
    @forelse($faculties as $faculty)
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            {{-- Faculty Header --}}
            <div class="card-header border-0 rounded-top-4 d-flex align-items-center justify-content-between py-3 px-4"
                 style="background: {{ $faculty->is_active ? 'linear-gradient(135deg, #1a2d5a 0%, #2d4a9a 100%)' : '#6c757d' }}; color: white;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                         style="width:46px;height:46px;background:rgba(255,255,255,0.15);font-size:1rem;">
                        {{ $faculty->code }}
                    </div>
                    <div>
                        <div class="fw-bold fs-5">{{ $faculty->name }}</div>
                        <div class="small opacity-75">
                            {{ $faculty->departments_count }} قسم ·
                            {{ $faculty->students_count }} طالب ·
                            {{ $faculty->payments_count }} معاملة
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    @if(!$faculty->is_active)
                        <span class="badge bg-light text-danger">معطّلة</span>
                    @endif
                    <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#editFaculty{{ $faculty->id }}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <form action="{{ route('admin.faculties.toggle', $faculty) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm {{ $faculty->is_active ? 'btn-warning' : 'btn-success' }}">
                            <i class="bi bi-{{ $faculty->is_active ? 'pause' : 'play' }}-fill"></i>
                        </button>
                    </form>
                    <button class="btn btn-sm btn-outline-light" data-bs-toggle="collapse" data-bs-target="#depts{{ $faculty->id }}">
                        <i class="bi bi-chevron-down"></i> الأقسام
                    </button>
                </div>
            </div>

            {{-- Departments --}}
            <div class="collapse show" id="depts{{ $faculty->id }}">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">اسم القسم/البرنامج</th>
                                <th>الكود</th>
                                <th>عدد الطلاب</th>
                                <th>الحالة</th>
                                <th class="pe-4">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($faculty->departments as $dept)
                            <tr>
                                <td class="ps-4 fw-bold">{{ $dept->name }}</td>
                                <td><span class="badge bg-light text-dark border font-monospace">{{ $dept->code }}</span></td>
                                <td class="text-muted">—</td>
                                <td>
                                    @if($dept->is_active)
                                        <span class="badge bg-success">مفعّل</span>
                                    @else
                                        <span class="badge bg-secondary">معطّل</span>
                                    @endif
                                </td>
                                <td class="pe-4">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editDept{{ $dept->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('admin.faculties.departments.toggle', [$faculty, $dept]) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-sm {{ $dept->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                                <i class="bi bi-{{ $dept->is_active ? 'pause' : 'play' }}-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                    {{-- Edit Dept Modal --}}
                                    <div class="modal fade" id="editDept{{ $dept->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <form action="{{ route('admin.faculties.departments.update', [$faculty, $dept]) }}" method="POST" class="modal-content">
                                                @csrf @method('PUT')
                                                <div class="modal-header border-0"><h5 class="modal-title fw-bold">تعديل القسم</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                                <div class="modal-body p-4">
                                                    <div class="mb-3"><label class="form-label fw-bold">اسم القسم</label><input type="text" name="name" class="form-control" value="{{ $dept->name }}" required></div>
                                                    <div class="mb-3"><label class="form-label fw-bold">الكود</label><input type="text" name="code" class="form-control" value="{{ $dept->code }}" required></div>
                                                </div>
                                                <div class="modal-footer border-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                                                    <button type="submit" class="btn btn-primary px-4">حفظ</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-folder-x me-2"></i>لا توجد أقسام بعد</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{-- Add Department --}}
                    <div class="p-3 border-top bg-light rounded-bottom">
                        <form action="{{ route('admin.faculties.departments.store', $faculty) }}" method="POST" class="d-flex gap-2 align-items-center">
                            @csrf
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="اسم القسم الجديد" required style="max-width:240px;">
                            <input type="text" name="code" class="form-control form-control-sm font-monospace" placeholder="الكود" required style="max-width:100px;">
                            <button class="btn btn-sm btn-primary px-3"><i class="bi bi-plus-lg me-1"></i> إضافة قسم</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        {{-- Edit Faculty Modal --}}
        <div class="modal fade" id="editFaculty{{ $faculty->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('admin.faculties.update', $faculty) }}" method="POST" class="modal-content">
                    @csrf @method('PUT')
                    <div class="modal-header border-0"><h5 class="modal-title fw-bold">تعديل الكلية</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body p-4">
                        <div class="mb-3"><label class="form-label fw-bold">اسم الكلية</label><input type="text" name="name" class="form-control" value="{{ $faculty->name }}" required></div>
                        <div class="mb-3"><label class="form-label fw-bold">الكود</label><input type="text" name="code" class="form-control font-monospace" value="{{ $faculty->code }}" required></div>
                        <div class="mb-3"><label class="form-label fw-bold">الوصف</label><textarea name="description" class="form-control" rows="2">{{ $faculty->description }}</textarea></div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary px-4">حفظ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5 text-muted">
        <i class="bi bi-building display-4 d-block mb-3"></i>
        لا توجد كليات مضافة بعد. ابدأ بإضافة أول كلية.
    </div>
    @endforelse
</div>

{{-- Add Faculty Modal --}}
<div class="modal fade" id="addFacultyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.faculties.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header border-0"><h5 class="modal-title fw-bold"><i class="bi bi-building me-2"></i>إضافة كلية جديدة</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">
                <div class="mb-3"><label class="form-label fw-bold">اسم الكلية <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required placeholder="مثال: كلية تكنولوجيا الصناعة والطاقة"></div>
                <div class="mb-3"><label class="form-label fw-bold">الكود المختصر <span class="text-danger">*</span></label><input type="text" name="code" class="form-control font-monospace" required placeholder="مثال: ENERGY" style="text-transform:uppercase;"></div>
                <div class="mb-3"><label class="form-label fw-bold">الوصف</label><textarea name="description" class="form-control" rows="2" placeholder="وصف مختصر للكلية"></textarea></div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-primary px-5">إضافة الكلية</button>
            </div>
        </form>
    </div>
</div>
@endsection
