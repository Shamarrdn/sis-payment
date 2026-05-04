@extends('layouts.app')

@section('title', 'إدارة الموظفين')
@section('page-heading', 'إدارة حسابات الموظفين والصلاحيات')
@section('user-name', auth()->user()->name)

@section('user-name', auth()->user()->name)

@section('content')
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h2 class="page-title">إدارة حسابات الموظفين</h2>
            <p class="page-subtitle mb-0">تحكم كامل في الأدوار والصلاحيات الدقيقة لكل موظف</p>
        </div>
        <button class="btn-primary-uni" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
            <i class="bi bi-person-plus-fill"></i> إضافة موظف جديد
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4 d-flex align-items-center" style="border-radius: 12px; background: rgba(16,185,129,0.1); color: #065f46;">
            <i class="bi bi-check-circle-fill me-3 fs-4"></i><div>{{ session('success') }}</div>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger mb-4">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    @endif

    @php
        $roleLabels = [
            'super_admin'      => ['label' => 'مدير النظام', 'bg' => '#1e3a5f', 'color' => '#fff'],
            'student_affairs'  => ['label' => 'شئون طلاب', 'bg' => 'rgba(15,23,42,0.05)', 'color' => '#0f172a'],
            'financial_affairs'=> ['label' => 'شئون مالية', 'bg' => 'rgba(212,175,55,0.1)', 'color' => '#854d0e'],
            'graduate_affairs' => ['label' => 'شئون خريجين', 'bg' => 'rgba(139,92,246,0.1)', 'color' => '#5b21b6'],
        ];
        $allPerms = \App\Models\User::availablePermissions();
    @endphp

    <div class="themed-table shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">الموظف</th>
                        <th>البريد الإلكتروني</th>
                        <th>الدور الوظيفي</th>
                        <th>نطاق العمل (Scope)</th>
                        <th>الصلاحيات</th>
                        <th>تاريخ الانضمام</th>
                        <th class="text-center pe-4">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $employee)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-sm rounded-circle bg-light d-flex align-items-center justify-content-center fw-bold text-primary"
                                     style="width:40px;height:40px;font-size:0.9rem;border:2px solid var(--accent);">
                                    {{ mb_substr($employee->name, 0, 1) }}
                                </div>
                                <div class="fw-bold">{{ $employee->name }}</div>
                            </div>
                        </td>
                        <td class="text-muted">{{ $employee->email }}</td>
                        <td>
                            @php $r = $roleLabels[$employee->role] ?? ['label' => $employee->role, 'bg' => '#eee', 'color' => '#333']; @endphp
                            <span class="badge px-3 py-2 rounded-pill"
                                  style="background:{{ $r['bg'] }};color:{{ $r['color'] }};border:1px solid rgba(0,0,0,0.08);">
                                {{ $r['label'] }}
                            </span>
                        </td>
                        <td>
                            @if($employee->role === 'super_admin')
                                <span class="badge bg-light text-dark border">كل الجامعة</span>
                            @elseif($employee->assignment)
                                <div class="fw-bold text-primary small">{{ $employee->assignment->faculty->name }}</div>
                                @if($employee->assignment->department)
                                    <div class="text-muted" style="font-size:0.75rem;">— {{ $employee->assignment->department->name }}</div>
                                @else
                                    <div class="text-muted small">كل الأقسام</div>
                                @endif
                            @else
                                <span class="text-muted small">كل الكليات (عام)</span>
                            @endif
                        </td>
                        <td>
                            @if($employee->role === 'super_admin')
                                <span class="badge bg-warning text-dark">كل الصلاحيات</span>
                            @elseif(!empty($employee->permissions))
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach(array_slice($employee->permissions, 0, 2) as $p)
                                        <span class="badge bg-light text-dark border" style="font-size:0.72rem;">{{ $allPerms[$p] ?? $p }}</span>
                                    @endforeach
                                    @if(count($employee->permissions) > 2)
                                        <span class="badge bg-secondary">+{{ count($employee->permissions) - 2 }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td><span class="text-muted">{{ $employee->created_at?->format('Y-m-d') ?? '---' }}</span></td>
                        <td class="text-center pe-4">
                            <div class="d-flex gap-1 justify-content-center">
                                <button class="btn btn-sm btn-light rounded-pill px-3 border"
                                        data-bs-toggle="modal" data-bs-target="#editEmployeeModal{{ $employee->id }}">
                                    <i class="bi bi-pencil-square text-primary me-1"></i> تعديل
                                </button>
                                <button class="btn btn-sm btn-light rounded-pill px-3 border"
                                        data-bs-toggle="modal" data-bs-target="#permModal{{ $employee->id }}">
                                    <i class="bi bi-shield-check text-success me-1"></i> صلاحيات
                                </button>
                                @if($employee->role !== 'super_admin')
                                <button class="btn btn-sm btn-light rounded-pill px-3 border"
                                        data-bs-toggle="modal" data-bs-target="#scopeModal{{ $employee->id }}">
                                    <i class="bi bi-geo-alt text-info me-1"></i> النطاق
                                </button>
                                @endif
                                @if($employee->role !== 'super_admin')
                                <form action="{{ route('admin.employees.delete', $employee) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light rounded-pill px-3 border"
                                            onclick="return confirm('هل أنت متأكد من حذف هذا الحساب؟')">
                                        <i class="bi bi-trash text-danger"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- Edit Modal --}}
                    <div class="modal fade" id="editEmployeeModal{{ $employee->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form action="{{ route('admin.employees.update', $employee) }}" method="POST" class="modal-content">
                                @csrf @method('PUT')
                                <div class="modal-header border-0"><h5 class="modal-title fw-bold">تحديث بيانات الموظف</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body p-4">
                                    <div class="mb-4">
                                        <label class="form-label">الاسم الكامل</label>
                                        <input type="text" name="name" class="form-control form-control-lg" value="{{ $employee->name }}" required>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label">البريد الإلكتروني</label>
                                        <input type="email" name="email" class="form-control form-control-lg" value="{{ $employee->email }}" required>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label">الدور الوظيفي</label>
                                        <select name="role" class="form-select form-select-lg" required>
                                            <option value="student_affairs"  {{ $employee->role === 'student_affairs'  ? 'selected' : '' }}>شئون طلاب</option>
                                            <option value="financial_affairs" {{ $employee->role === 'financial_affairs' ? 'selected' : '' }}>شئون مالية</option>
                                            <option value="graduate_affairs"  {{ $employee->role === 'graduate_affairs'  ? 'selected' : '' }}>شئون خريجين</option>
                                            <option value="super_admin"       {{ $employee->role === 'super_admin'       ? 'selected' : '' }}>مدير النظام</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">كلمة مرور جديدة (اختياري)</label>
                                        <input type="password" name="password" class="form-control form-control-lg" placeholder="اتركها فارغة لعدم التغيير">
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                                    <button type="submit" class="btn-primary-uni rounded-pill px-5">تحديث الحساب</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Permissions Modal --}}
                    <div class="modal fade" id="permModal{{ $employee->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form action="{{ route('admin.employees.permissions', $employee) }}" method="POST" class="modal-content">
                                @csrf
                                <div class="modal-header border-0">
                                    <h5 class="modal-title fw-bold"><i class="bi bi-shield-check me-2 text-success"></i>صلاحيات: {{ $employee->name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    @if($employee->role === 'super_admin')
                                        <div class="alert alert-warning"><i class="bi bi-star-fill me-2"></i>المدير العام يمتلك كل الصلاحيات تلقائياً.</div>
                                    @else
                                        <p class="text-muted small mb-3">حدد الصلاحيات الإضافية الممنوحة لهذا الموظف خارج دوره الأساسي:</p>
                                        <div class="d-flex flex-column gap-2">
                                            @foreach($allPerms as $key => $label)
                                            <div class="form-check p-3 rounded-3" style="background:#f8fafc;border:1.5px solid #e2e8f0;">
                                                <input class="form-check-input" type="checkbox"
                                                       name="{{ $key }}"
                                                       id="perm_{{ $employee->id }}_{{ $key }}"
                                                       {{ in_array($key, $employee->permissions ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="perm_{{ $employee->id }}_{{ $key }}">
                                                    {{ $label }}
                                                </label>
                                            </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                                    @if($employee->role !== 'super_admin')
                                    <button type="submit" class="btn btn-success rounded-pill px-5">حفظ الصلاحيات</button>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                    {{-- Scope Modal --}}
                    @if($employee->role !== 'super_admin')
                    <div class="modal fade" id="scopeModal{{ $employee->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form action="{{ route('admin.employees.assignment', $employee) }}" method="POST" class="modal-content">
                                @csrf
                                <div class="modal-header border-0">
                                    <h5 class="modal-title fw-bold"><i class="bi bi-geo-alt me-2 text-info"></i>تحديد نطاق العمل: {{ $employee->name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <p class="text-muted small mb-4">الموظف سيمكنه فقط رؤية والتعامل مع طلاب ومدفوعات النطاق المحدد هنا.</p>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">الكلية</label>
                                        <select name="faculty_id" class="form-select faculty-scope-select" data-user-id="{{ $employee->id }}">
                                            <option value="">كل الكليات (وصول عام)</option>
                                            @foreach($faculties as $f)
                                                <option value="{{ $f->id }}" {{ ($employee->assignment?->faculty_id == $f->id) ? 'selected' : '' }}>{{ $f->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">القسم (اختياري)</label>
                                        <select name="department_id" class="form-select department-scope-select" id="dept_select_{{ $employee->id }}">
                                            <option value="">كل أقسام الكلية</option>
                                            @if($employee->assignment?->faculty_id)
                                                @foreach($faculties->firstWhere('id', $employee->assignment->faculty_id)->activeDepartments as $d)
                                                    <option value="{{ $d->id }}" {{ ($employee->assignment?->department_id == $d->id) ? 'selected' : '' }}>{{ $d->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>

                                    @if($employee->assignment)
                                    <div class="mt-4 pt-3 border-top">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="small text-muted">إزالة كل القيود؟</span>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="document.getElementById('clearForm{{ $employee->id }}').submit()">
                                                <i class="bi bi-unlock me-1"></i> مسح النطاق (وصول عام)
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                                    <button type="submit" class="btn btn-info text-white rounded-pill px-5">حفظ التعيين</button>
                                </div>
                            </form>
                            @if($employee->assignment)
                            <form id="clearForm{{ $employee->id }}" action="{{ route('admin.employees.assignment.clear', $employee) }}" method="POST" style="display:none;">
                                @csrf @method('DELETE')
                            </form>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
    const scopesData = @json($faculties->mapWithKeys(fn($f) => [$f->id => $f->activeDepartments->map(fn($d) => ['id' => $d->id, 'name' => $d->name])]));

    document.querySelectorAll('.faculty-scope-select').forEach(sel => {
        sel.addEventListener('change', function() {
            const userId = this.getAttribute('data-user-id');
            const deptSel = document.getElementById('dept_select_' + userId);
            deptSel.innerHTML = '<option value="">كل أقسام الكلية</option>';
            
            const depts = scopesData[this.value] || [];
            depts.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id;
                opt.textContent = d.name;
                deptSel.appendChild(opt);
            });
        });
    });
    </script>

    {{-- Add Employee Modal --}}
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('admin.employees.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header border-0"><h5 class="modal-title fw-bold">إضافة موظف جديد للمنظومة</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-4">
                    <div class="mb-4"><label class="form-label">اسم الموظف</label><input type="text" name="name" class="form-control form-control-lg" required></div>
                    <div class="mb-4"><label class="form-label">البريد الجامعي</label><input type="email" name="email" class="form-control form-control-lg" required></div>
                    <div class="mb-4">
                        <label class="form-label">الصلاحية</label>
                        <select name="role" class="form-select form-select-lg" required>
                            <option value="student_affairs">شئون طلاب</option>
                            <option value="financial_affairs">شئون مالية</option>
                            <option value="graduate_affairs">شئون خريجين</option>
                            <option value="super_admin">مدير النظام</option>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">كلمة المرور الافتراضية</label><input type="password" name="password" class="form-control form-control-lg" required></div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn-primary-uni rounded-pill px-5">إنشاء الحساب</button>
                </div>
            </form>
        </div>
    </div>
@endsection
