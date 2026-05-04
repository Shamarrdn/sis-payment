@extends('layouts.app')
@section('title', 'إعدادات الرسوم الدراسية')
@section('page-heading', 'إعدادات الرسوم الدراسية الديناميكية')
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
        <h2 class="page-title mb-1">إعدادات الرسوم الدراسية</h2>
        <p class="page-subtitle mb-0">النظام يبحث عن أدق إعداد ينطبق على الطالب (كلية + قسم + فرقة + فئة)</p>
    </div>
    <button class="btn-primary-uni" data-bs-toggle="modal" data-bs-target="#addConfigModal">
        <i class="bi bi-plus-lg me-1"></i> إضافة إعداد رسوم
    </button>
</div>

{{-- Priority Explanation --}}
<div class="alert border-0 shadow-sm mb-4 rounded-3" style="background:rgba(59,130,246,0.05);border-left:4px solid #3b82f6!important;">
    <div class="d-flex gap-3 align-items-start">
        <i class="bi bi-info-circle-fill text-primary fs-4 mt-1"></i>
        <div>
            <div class="fw-bold text-primary mb-1">كيف يُحدَّد المبلغ للطالب؟</div>
            <div class="text-muted small">
                النظام يبحث بالأولوية التالية:
                <strong>كلية+قسم+فرقة+فئة</strong> ثم
                <strong>كلية+قسم+فرقة</strong> ثم
                <strong>كلية+فرقة</strong> ثم
                <strong>كلية فقط</strong> ثم
                <strong>إعداد عام للفرقة</strong> ثم
                <strong>الإعداد الافتراضي</strong>.
                الإعداد الأكثر تحديداً يُقدَّم دائماً.
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">الكلية</th>
                    <th>القسم/البرنامج</th>
                    <th>الفرقة</th>
                    <th>الفئة</th>
                    <th>الرسوم</th>
                    <th>الرسم الإضافي</th>
                    <th>الإجمالي</th>
                    <th>من تاريخ</th>
                    <th>إلى تاريخ</th>
                    <th>الحالة</th>
                    <th>آخر تعديل</th>
                    <th class="pe-4">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($configs as $config)
                <tr class="{{ !$config->is_active ? 'table-secondary opacity-75' : '' }}">
                    <td class="ps-4">
                        @if($config->faculty)
                            <span class="badge bg-primary">{{ $config->faculty->code }}</span>
                            <div class="small text-muted">{{ $config->faculty->name }}</div>
                        @else
                            <span class="badge bg-light text-muted border">كل الكليات</span>
                        @endif
                    </td>
                    <td>
                        @if($config->department)
                            <span class="badge bg-info text-dark">{{ $config->department->code }}</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>{{ $config->academic_year ?? "----" }}</td>
                    <td>{{ $config->user_category ?? "----" }}</td>
                    <td class="fw-bold">{{ number_format($config->tuition_amount) }} <small class="text-muted">ج.م</small></td>
                    <td class="text-muted">{{ number_format($config->extra_fee) }}</td>
                    <td class="fw-bold text-success">{{ number_format($config->totalAmount()) }} <small>ج.م</small></td>
                    <td class="small">{{ $config->effective_from->format('Y-m-d') }}</td>
                    <td class="small text-muted">{{ $config->effective_to?->format('Y-m-d') ?? '∞' }}</td>
                    <td>
                        @if($config->is_active)
                            <span class="badge bg-success">نشط</span>
                        @else
                            <span class="badge bg-secondary">متوقف</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $config->updatedBy?->name ?? '—' }}</td>
                    <td class="pe-4">
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editConfig{{ $config->id }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('admin.tuition.destroy', $config) }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('حذف هذا الإعداد؟')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                        {{-- Edit Modal --}}
                        <div class="modal fade" id="editConfig{{ $config->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="{{ route('admin.tuition.update', $config) }}" method="POST" class="modal-content">
                                    @csrf @method('PUT')
                                    <div class="modal-header border-0"><h5 class="modal-title fw-bold">تعديل الإعداد</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body p-4">
                                        <div class="alert alert-info small py-2">{{ $config->label() }}</div>
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label fw-bold">الرسوم الدراسية</label><input type="number" name="tuition_amount" class="form-control" value="{{ $config->tuition_amount }}" required></div>
                                            <div class="col-md-6"><label class="form-label fw-bold">الرسم الإضافي</label><input type="number" name="extra_fee" class="form-control" value="{{ $config->extra_fee }}"></div>
                                            <div class="col-md-6"><label class="form-label fw-bold">من تاريخ</label><input type="date" name="effective_from" class="form-control" value="{{ $config->effective_from->format('Y-m-d') }}" required></div>
                                            <div class="col-md-6"><label class="form-label fw-bold">إلى تاريخ</label><input type="date" name="effective_to" class="form-control" value="{{ $config->effective_to?->format('Y-m-d') }}"></div>
                                            <div class="col-12"><label class="form-label fw-bold">ملاحظات</label><textarea name="notes" class="form-control" rows="2">{{ $config->notes }}</textarea></div>
                                            <div class="col-12">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="is_active" {{ $config->is_active ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold">نشط</label>
                                                </div>
                                            </div>
                                        </div>
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
                <tr><td colspan="12" class="text-center py-5 text-muted"><i class="bi bi-cash-stack display-4 d-block mb-3"></i>لا توجد إعدادات رسوم بعد. أضف أول إعداد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($configs->hasPages())
    <div class="p-4">{{ $configs->links() }}</div>
    @endif
</div>

{{-- Add Config Modal --}}
<div class="modal fade" id="addConfigModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('admin.tuition.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header border-0"><h5 class="modal-title fw-bold"><i class="bi bi-cash-stack me-2 text-primary"></i>إضافة إعداد رسوم جديد</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">
                <div class="alert alert-info small py-2 mb-4">
                    <i class="bi bi-lightbulb me-2"></i>
                    اترك أي حقل فارغاً لجعله ينطبق على <strong>الكل</strong>. مثلاً: بدون كلية = ينطبق على جميع الكليات.
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">الكلية</label>
                        <select name="faculty_id" id="addFacultySelect" class="form-select">
                            <option value="">كل الكليات</option>
                            @foreach($faculties as $fac)
                                <option value="{{ $fac->id }}">{{ $fac->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">القسم/البرنامج</label>
                        <select name="department_id" id="addDeptSelect" class="form-select">
                            <option value="">كل الأقسام</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">الفرقة/المستوى</label>
                        <select name="academic_year" class="form-select">
                            <option value="">كل الفرق</option>
                            @foreach($years as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">فئة الطالب</label>
                        <select name="user_category" class="form-select">
                            <option value="">كل الفئات</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">الرسوم الدراسية <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="tuition_amount" class="form-control" required min="0" step="0.01" placeholder="مثال: 15000">
                            <span class="input-group-text">ج.م</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">الرسم الإضافي</label>
                        <div class="input-group">
                            <input type="number" name="extra_fee" class="form-control" min="0" step="0.01" value="0">
                            <span class="input-group-text">ج.م</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">تاريخ السريان <span class="text-danger">*</span></label>
                        <input type="date" name="effective_from" class="form-control" required value="{{ today()->toDateString() }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">تاريخ الانتهاء</label>
                        <input type="date" name="effective_to" class="form-control">
                        <div class="form-text">اتركه فارغاً = ساري حتى إشعار آخر</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="مثال: رسوم السنة الدراسية 2025-2026"></textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" checked>
                            <label class="form-check-label fw-bold">تفعيل هذا الإعداد فور الإضافة</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-primary px-5">إضافة الإعداد</button>
            </div>
        </form>
    </div>
</div>

<script>
// Dynamic department loading based on selected faculty
const deptData = @json($faculties->mapWithKeys(fn($f) => [$f->id => $f->activeDepartments->map(fn($d) => ['id' => $d->id, 'name' => $d->name])]));

document.getElementById('addFacultySelect')?.addEventListener('change', function() {
    const sel = document.getElementById('addDeptSelect');
    sel.innerHTML = '<option value="">كل الأقسام</option>';
    const depts = deptData[this.value] || [];
    depts.forEach(d => {
        const opt = document.createElement('option');
        opt.value = d.id;
        opt.textContent = d.name;
        sel.appendChild(opt);
    });
});
</script>
@endsection
