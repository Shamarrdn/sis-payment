@extends('layouts.app')

@section('title', 'شئون الطلاب - إدارة الطلاب')
@section('page-heading', 'إدارة الطلاب')
@section('user-name', auth()->user()->name)

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="page-title">الطلاب المسجلون</h2>
        <p class="page-subtitle">عرض وإدارة بيانات جميع الطلاب في النظام</p>
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

<div class="themed-table">
    <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>الرقم القومي</th>
                <th>الرقم المرجعي</th>
                <th>الفرقة / السنة</th>
                <th>البرنامج</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
                <tr>
                    <td><span class="badge rounded-pill" style="background:#e8eef6;color:#1a2d5a;font-weight:600;">{{ $student->id }}</span></td>
                    <td><strong>{{ $student->name }}</strong></td>
                    <td><code class="text-muted">{{ $student->national_id }}</code></td>
                    <td><span class="badge bg-secondary rounded-pill">{{ $student->reference_number }}</span></td>
                    <td>{{ $student->academic_year }}</td>
                    <td>{{ $student->program }}</td>
                    <td>
                        <a href="{{ route('affairs.student.receipts', $student) }}" class="btn btn-sm btn-light rounded-pill border">
                            <i class="bi bi-receipt text-primary me-1"></i> الإيصالات
                        </a>
                        @if(auth()->user()->hasPermission('manual_cash_entry'))
                        <button class="btn btn-sm btn-light rounded-pill border" 
                                data-bs-toggle="modal" data-bs-target="#manualPayModal{{ $student->id }}">
                            <i class="bi bi-cash-stack text-success me-1"></i> سداد يدوي
                        </button>
                        @endif
                    </td>
                </tr>
                @if(auth()->user()->hasPermission('manual_cash_entry'))
                {{-- Manual Payment Modal --}}
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
                @endif
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-inbox display-5 d-block mb-2 text-secondary"></i>
                        لا يوجد طلاب مسجلون حتى الآن.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $students->links() }}
</div>

@section('scripts')
<script>
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
                    "{{ $s->id }}": {{ $s->resolution['total'] }},
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
</script>
@endsection
@endsection
