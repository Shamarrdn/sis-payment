@extends('layouts.app')

@section('title', 'شئون الطلاب - إدارة الطلاب')
@section('page-heading', 'إدارة الطلاب')
@section('user-name', auth()->user()->name)

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="page-title">الطلاب المسجلون</h2>
        <p class="page-subtitle">عرض وإدارة بيانات جميع الطلاب وتدقيق مستنداتهم في النظام</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        {{-- CSV Template Download + Import --}}
        <div class="d-flex align-items-center gap-2 bg-white rounded-3 p-2 shadow-sm border">
            <form action="{{ route('affairs.student.import') }}" method="POST" enctype="multipart/form-data"
                  class="d-flex align-items-center gap-2">
                @csrf
                <input type="file" name="file" class="form-control form-control-sm" accept=".csv" required style="max-width:200px;">
                <button type="submit" class="btn-primary-uni" style="padding:8px 14px;font-size:0.85rem;">
                    <i class="bi bi-file-earmark-excel"></i> استيراد CSV
                </button>
            </form>
            <a href="{{ route('affairs.student.csv-template') }}"
               class="btn btn-sm btn-outline-secondary rounded-pill"
               title="تحميل قالب CSV جاهز للتعبئة">
                <i class="bi bi-download me-1"></i> قالب CSV
            </a>
        </div>
        <a href="{{ route('affairs.student.create') }}" class="btn-primary-uni">
            <i class="bi bi-plus-circle-fill"></i> إضافة طالب
        </a>
        <a href="{{ route('affairs.student.export', request()->query()) }}" class="btn btn-sm btn-outline-success rounded-pill">
            <i class="bi bi-file-earmark-excel"></i> Excel
        </a>
        <a href="{{ route('affairs.student.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill">
            <i class="bi bi-file-pdf"></i> PDF
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm mb-4 d-flex align-items-center" style="border-radius:12px;background:rgba(16,185,129,0.1);color:#065f46;">
        <i class="bi bi-check-circle-fill me-3 fs-4"></i><div>{{ session('success') }}</div>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger mb-4 shadow-sm" style="border-radius:12px;">
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
@endif

@if(session('import_report') && !empty(session('import_report.errors')))
<div class="card mb-4 border-warning shadow-sm" style="border-radius:12px;">
    <div class="card-header bg-warning bg-opacity-10 border-0 pt-3 px-4 pb-0">
        <h6 class="fw-bold text-warning mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>تقرير الاستيراد — صفوف تم تخطيها</h6>
        <p class="text-muted small mb-3">تم استيراد <strong>{{ session('import_report.imported') }}</strong> طالب. تم تخطي <strong>{{ session('import_report.skipped') }}</strong> صفوف بسبب:</p>
    </div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush" style="max-height:200px;overflow-y:auto;">
            @foreach(session('import_report.errors') as $err)
                <li class="list-group-item text-danger small py-2 px-4"><i class="bi bi-x-circle me-2"></i>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

{{-- Advanced Search & Filters Card --}}
<div class="card mb-4 border-0 shadow-sm" style="border-radius: 16px;">
    <div class="card-body p-4">
        <h5 class="fw-bold text-primary mb-3"><i class="bi bi-funnel-fill me-2"></i>أدوات البحث والتصفية المتقدمة</h5>
        <form action="{{ route('affairs.student.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">بحث نصي</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="الاسم، الرقم القومي، الرقم المرجعي...">
            </div>
            <div class="col-md-2.5 col-lg-2">
                <label class="form-label">الكلية</label>
                <select name="faculty_id" id="filterFacultySelect" class="form-select">
                    <option value="">كل الكليات</option>
                    @foreach($faculties as $f)
                        <option value="{{ $f->id }}" {{ request('faculty_id') == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2.5 col-lg-2">
                <label class="form-label">القسم</label>
                <select name="department_id" id="filterDepartmentSelect" class="form-select">
                    <option value="">كل الأقسام</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">الحالة الأكاديمية</label>
                <select name="status" class="form-select">
                    <option value="">الكل</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>موقوف</option>
                    <option value="graduated" {{ request('status') == 'graduated' ? 'selected' : '' }}>خريج</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">حالة اكتمال الملف</label>
                <select name="completion" class="form-select">
                    <option value="">الكل</option>
                    <option value="complete" {{ request('completion') == 'complete' ? 'selected' : '' }}>مكتمل (100%)</option>
                    <option value="incomplete" {{ request('completion') == 'incomplete' ? 'selected' : '' }}>غير مكتمل (<100%)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">مستندات مفقودة</label>
                <select name="missing_document" class="form-select">
                    <option value="">الكل</option>
                    <option value="national_id" {{ request('missing_document') == 'national_id' ? 'selected' : '' }}>بطاقة الرقم القومي</option>
                    <option value="birth_certificate" {{ request('missing_document') == 'birth_certificate' ? 'selected' : '' }}>شهادة الميلاد</option>
                    <option value="personal_photo" {{ request('missing_document') == 'personal_photo' ? 'selected' : '' }}>الصورة الشخصية</option>
                </select>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('affairs.student.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> إعادة تعيين
                </a>
                <button type="submit" class="btn-primary-uni px-5 rounded-pill">
                    <i class="bi bi-search me-1"></i> تصفية النتائج
                </button>
            </div>
        </form>
    </div>
</div>

<form action="{{ route('affairs.student.bulk-action') }}" method="POST" id="bulkActionForm">
    @csrf
    
    {{-- Bulk Action Card --}}
    <div id="bulkActionCard" class="card mb-4 border-0 shadow-sm border-start border-primary border-4" style="border-radius: 12px; display: none; background: #f8fafc;">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-check-all me-2"></i>الإجراءات الجماعية للطلاب المحددين (<span id="selectedCount">0</span> طالب محدد)</h5>
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">نوع الإجراء</label>
                    <select name="action_type" id="bulkActionType" class="form-select" required>
                        <option value="">-- اختر إجراء --</option>
                        <option value="update_status">تحديث الحالة الأكاديمية</option>
                        <option value="add_note">إضافة ملاحظة داخلية جماعية</option>
                        <option value="verify_documents">اعتماد وقبول جميع المستندات المعلقة</option>
                    </select>
                </div>
                
                {{-- Status fields --}}
                <div class="col-md-3 bulk-field-group" id="bulkStatusGroup" style="display: none;">
                    <label class="form-label">الحالة الجديدة</label>
                    <select name="status" class="form-select">
                        <option value="active">نشط</option>
                        <option value="suspended">موقوف</option>
                        <option value="graduated">خريج</option>
                    </select>
                </div>
                <div class="col-md-4 bulk-field-group" id="bulkStatusNotesGroup" style="display: none;">
                    <label class="form-label">ملاحظات تغيير الحالة (إلزامي)</label>
                    <input type="text" name="status_notes" class="form-control" placeholder="سبب تغيير الحالة...">
                </div>

                {{-- Note field --}}
                <div class="col-md-7 bulk-field-group" id="bulkNoteGroup" style="display: none;">
                    <label class="form-label">نص الملاحظة الداخلية</label>
                    <input type="text" name="note" class="form-control" placeholder="اكتب الملاحظة الداخلية هنا...">
                </div>

                <div class="col-md-2 text-end">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold" style="background:var(--primary); border: none;">
                        <i class="bi bi-lightning-fill me-1"></i> تنفيذ الإجراء
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="themed-table">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th width="40">
                        <input type="checkbox" id="selectAllCheckboxes" class="form-check-input">
                    </th>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>الرقم القومي</th>
                    <th>الرقم المرجعي</th>
                    <th>الكلية والفرقة</th>
                    <th>اكتمال الملف</th>
                    <th>الحالة الأكاديمية</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    <tr>
                        <td>
                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="form-check-input student-checkbox">
                        </td>
                        <td><span class="badge rounded-pill" style="background:#e8eef6;color:#1a2d5a;font-weight:600;">{{ $student->id }}</span></td>
                        <td>
                            <a href="{{ route('affairs.student.show', $student) }}" class="text-decoration-none text-dark fw-bold hover-primary">
                                {{ $student->name }}
                            </a>
                        </td>
                        <td><code class="text-muted">{{ $student->national_id }}</code></td>
                        <td><span class="badge bg-secondary rounded-pill">{{ $student->reference_number }}</span></td>
                        <td>
                            <span class="d-block fw-semibold text-secondary" style="font-size: 0.85rem;">
                                {{ $student->facultyName() }}
                            </span>
                            <span class="small text-muted" style="font-size: 0.78rem;">
                                {{ $student->academic_year }} @if($student->departmentName() !== '—') - {{ $student->departmentName() }} @endif
                            </span>
                        </td>
                        <td>
                            @php
                                $percent = $student->completionPercentage();
                            @endphp
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress" style="height: 6px; width: 60px; border-radius: 3px;">
                                    <div class="progress-bar {{ $percent === 100 ? 'bg-success' : 'bg-warning' }}" role="progressbar" style="width: {{ $percent }}%"></div>
                                </div>
                                <span class="small fw-bold {{ $percent === 100 ? 'text-success' : 'text-warning' }}">{{ $percent }}%</span>
                            </div>
                        </td>
                        <td>
                            @if($student->status === 'active')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1" style="font-size:0.8rem;">نشط</span>
                            @elseif($student->status === 'suspended')
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1" style="font-size:0.8rem;">موقوف</span>
                            @elseif($student->status === 'graduated')
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1" style="font-size:0.8rem;">خريج</span>
                            @else
                                <span class="badge bg-secondary px-2 py-1" style="font-size:0.8rem;">غير محدد</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('affairs.student.show', $student) }}" class="btn btn-sm btn-light rounded-pill border" title="الملف الإداري والمستندات">
                                    <i class="bi bi-person-badge-fill text-dark me-1"></i> الملف الإداري
                                </a>
                                <a href="{{ route('affairs.student.receipts', $student) }}" class="btn btn-sm btn-light rounded-pill border" title="الأرشيف الرقمي">
                                    <i class="bi bi-archive-fill text-primary me-1"></i> الأرشيف
                                </a>
                                @if(auth()->user()->hasPermission('manual_cash_entry'))
                                <button type="button" class="btn btn-sm btn-light rounded-pill border" 
                                        data-bs-toggle="modal" data-bs-target="#manualPayModal{{ $student->id }}">
                                    <i class="bi bi-cash-stack text-success me-1"></i> سداد يدوي
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="bi bi-inbox display-5 d-block mb-2 text-secondary"></i>
                            لا يوجد طلاب يطابقون خيارات البحث الحالية.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</form>

{{-- Manual Payment Modals --}}
@if(auth()->user()->hasPermission('manual_cash_entry'))
    @foreach($students as $student)
        <div class="modal fade" id="manualPayModal{{ $student->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('affairs.student.manual-pay', $student) }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold"><i class="bi bi-cash-stack me-2 text-success"></i>إدخال سداد يدوي: {{ $student->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label">اختر الخدمة</label>
                            <select name="service_id" class="form-select service-select" data-student-id="{{ $student->id }}" required>
                                <option value="">-- اختر الخدمة --</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" 
                                            data-amount="{{ $service->amount }}"
                                            data-is-tuition="{{ str_contains($service->type, 'مصاريف') ? 'true' : 'false' }}">
                                        {{ $service->name }} ({{ number_format($service->amount) }} ج.م)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">المبلغ المطلوب سداده</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="amount" id="amount_{{ $student->id }}" class="form-control" placeholder="0.00" required>
                                <span class="input-group-text bg-light fw-bold">ج.م</span>
                            </div>
                            <div class="form-text text-primary small" id="hint_{{ $student->id }}" style="display:none;">
                                <i class="bi bi-info-circle me-1"></i> تم تحديد المبلغ بناءً على محرك الرسوم الذكي للطالب.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الرقم المرجعي (اختياري)</label>
                            <input type="text" name="reference_number" class="form-control" placeholder="مثلاً رقم إيصال يدوي">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">ملاحظات إضافية</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="ضع أي تفاصيل إضافية هنا..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn-primary-uni rounded-pill px-5">تأكيد وقيد العملية</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endif

<div class="d-flex justify-content-center mt-4">
    {{ $students->appends(request()->query())->links() }}
</div>

@section('scripts')
<script>
    // Manual Payment Select Service Change handler
    document.querySelectorAll('.service-select').forEach(select => {
        select.addEventListener('change', function() {
            const studentId = this.dataset.studentId;
            const option = this.options[this.selectedIndex];
            const amountInput = document.getElementById('amount_' + studentId);
            const hint = document.getElementById('hint_' + studentId);
            
            if (!option.value) {
                amountInput.value = '';
                hint.style.display = 'none';
                return;
            }

            const isTuition = option.dataset.isTuition === 'true';
            
            // Map students and their resolutions to a JS object
            const resolutions = {
                @foreach($students as $s)
                    "{{ $s->id }}": {{ $s->resolution['total'] ?? $s->resolution ?? 0 }},
                @endforeach
            };

            if (isTuition) {
                amountInput.value = resolutions[studentId];
                hint.style.display = 'block';
            } else {
                amountInput.value = option.dataset.amount;
                hint.style.display = 'none';
            }
        });
    });

    // Faculty -> Department dynamically populated in the filters
    const filterFacultySelect = document.getElementById('filterFacultySelect');
    const filterDepartmentSelect = document.getElementById('filterDepartmentSelect');

    const facultyDepartments = @json($faculties->mapWithKeys(function ($faculty) {
        return [$faculty->id => $faculty->activeDepartments->map(function ($dept) {
            return ['id' => $dept->id, 'name' => $dept->name];
        })->toArray()];
    }));

    function updateFilterDepartments(selectedFacultyId, selectedDeptId = null) {
        filterDepartmentSelect.innerHTML = '<option value="">كل الأقسام</option>';
        if (selectedFacultyId && facultyDepartments[selectedFacultyId]) {
            facultyDepartments[selectedFacultyId].forEach(dept => {
                const option = document.createElement('option');
                option.value = dept.id;
                option.textContent = dept.name;
                if (selectedDeptId && dept.id == selectedDeptId) {
                    option.selected = true;
                }
                filterDepartmentSelect.appendChild(option);
            });
        }
    }

    if (filterFacultySelect) {
        filterFacultySelect.addEventListener('change', function() {
            updateFilterDepartments(this.value);
        });
        
        // Initialize on load if faculty is selected
        const initialFaculty = filterFacultySelect.value;
        const initialDept = "{{ request('department_id') }}";
        if (initialFaculty) {
            updateFilterDepartments(initialFaculty, initialDept);
        }
    }

    // Bulk Action script
    const selectAllCheckboxes = document.getElementById('selectAllCheckboxes');
    const studentCheckboxes = document.querySelectorAll('.student-checkbox');
    const bulkActionCard = document.getElementById('bulkActionCard');
    const selectedCountSpan = document.getElementById('selectedCount');
    const bulkActionType = document.getElementById('bulkActionType');

    function updateBulkActionCard() {
        const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
        const count = checkedBoxes.length;
        selectedCountSpan.textContent = count;
        if (count > 0) {
            bulkActionCard.style.display = 'block';
        } else {
            bulkActionCard.style.display = 'none';
        }
    }

    if (selectAllCheckboxes) {
        selectAllCheckboxes.addEventListener('change', function() {
            studentCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            updateBulkActionCard();
        });
    }

    studentCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            updateBulkActionCard();
            // sync select all checkbox status
            const allChecked = document.querySelectorAll('.student-checkbox:checked').length === studentCheckboxes.length;
            selectAllCheckboxes.checked = allChecked;
        });
    });

    if (bulkActionType) {
        bulkActionType.addEventListener('change', function() {
            // Hide all sub-fields first
            document.querySelectorAll('.bulk-field-group').forEach(group => {
                group.style.display = 'none';
                group.querySelectorAll('input, select, textarea').forEach(input => {
                    input.required = false;
                });
            });

            const action = this.value;
            if (action === 'update_status') {
                document.getElementById('bulkStatusGroup').style.display = 'block';
                document.getElementById('bulkStatusNotesGroup').style.display = 'block';
                document.querySelector('input[name="status_notes"]').required = true;
            } else if (action === 'add_note') {
                document.getElementById('bulkNoteGroup').style.display = 'block';
                document.querySelector('input[name="note"]').required = true;
            }
        });
    }
</script>
@endsection
@endsection
