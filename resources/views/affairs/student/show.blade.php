@extends('layouts.app')

@section('title', 'تفاصيل الطالب - ' . $student->name)
@section('page-heading', 'الملف الإداري للطالب')
@section('user-name', auth()->user()->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('affairs.student.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-arrow-right me-1"></i> العودة لقائمة الطلاب
    </a>
</div>

<div class="row g-4">
    {{-- Left Side: Profile Details, Pending Sensitive Requests, Documents, Notes --}}
    <div class="col-lg-8">
        {{-- Profile Card --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.8rem;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1">{{ $student->name }}</h4>
                        <p class="text-muted mb-0">رقم مرجعي: <span class="badge bg-secondary">{{ $student->reference_number }}</span></p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <span class="text-muted d-block small">الرقم القومي</span>
                        <strong class="text-dark">{{ $student->national_id }}</strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted d-block small">الكلية والبرنامج</span>
                        <strong class="text-dark">{{ $student->facultyName() }} @if($student->departmentName() !== '—') - {{ $student->departmentName() }} @endif</strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted d-block small">الفرقة الدراسية</span>
                        <strong class="text-dark">{{ $student->academic_year ?: 'غير محدد' }}</strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted d-block small">رقم الهاتف</span>
                        <strong class="text-dark">{{ $student->phone ?: 'غير محدد' }}</strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted d-block small">البريد الإلكتروني</span>
                        <strong class="text-dark">{{ $student->email ?: 'غير محدد' }}</strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted d-block small">العنوان السكني</span>
                        <strong class="text-dark">{{ $student->address ?: 'غير محدد' }}</strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted d-block small">فئة المستخدم</span>
                        <strong class="text-dark">{{ $student->user_category ?: 'طالب' }}</strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted d-block small">الفئة الخاصة</span>
                        <strong class="text-dark">{{ $student->special_category ?: 'لا يوجد إعفاء خاص' }}</strong>
                    </div>
                </div>

                <hr class="my-4">

                {{-- Profile Completion Percentage --}}
                @php
                    $percentage = $student->completionPercentage();
                    $checklist = $student->missingChecklist();
                @endphp
                <div class="bg-light rounded-3 p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold text-secondary">حالة اكتمال الملف الشخصي</span>
                        <span class="badge bg-primary fs-6">{{ $percentage }}%</span>
                    </div>
                    <div class="progress mb-3" style="height: 10px; border-radius: 5px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%"></div>
                    </div>
                    
                    @if(empty($checklist))
                        <div class="text-success small d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill"></i> جميع البيانات الشخصية والمستندات المطلوبة كاملة ومكتملة.
                        </div>
                    @else
                        <span class="text-muted small d-block mb-2">المتطلبات غير المكتملة أو المرفوضة:</span>
                        <div class="row row-cols-1 row-cols-md-2 g-2">
                            @foreach($checklist as $item)
                                <div class="col text-danger small d-flex align-items-center gap-2">
                                    <i class="bi bi-x-circle-fill"></i>
                                    <span>{{ $item['label'] }}</span>
                                    @if(isset($item['status']) && $item['status'] === 'rejected')
                                        <span class="badge bg-danger text-white rounded-pill" style="font-size:0.65rem;" title="{{ $item['reason'] }}">مرفوض</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sensitive Data Change Requests Section --}}
        @php
            $pendingRequest = $student->sensitiveDataRequests->where('status', 'pending')->first();
        @endphp
        @if($pendingRequest)
            <div class="card border-0 shadow-sm mb-4 border-start border-warning border-4" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-warning mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i>طلب معلق لتعديل بيانات حساسة</h5>
                        <span class="badge bg-warning text-dark">قيد المراجعة</span>
                    </div>
                    <p class="text-muted small">قام الطالب بطلب تعديل الحقول التالية. يرجى مراجعة البيانات الجديدة ومطابقتها قبل اتخاذ قرار الاعتماد أو الرفض.</p>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm small align-middle mb-4">
                            <thead class="table-light">
                                <tr>
                                    <th>الحقل</th>
                                    <th>القيمة الحالية</th>
                                    <th>القيمة المطلوبة الجديدة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingRequest->requested_data as $key => $val)
                                    @php
                                        $label = match($key) {
                                            'name' => 'الاسم الكامل',
                                            'national_id' => 'الرقم القومي',
                                            'faculty_id' => 'الكلية',
                                            'department_id' => 'القسم',
                                            default => $key
                                        };

                                        // Fetch actual labels if relational
                                        $oldVal = $student->{$key};
                                        if ($key === 'faculty_id') {
                                            $oldVal = \App\Models\Faculty::find($oldVal)?->name ?? '—';
                                            $val = \App\Models\Faculty::find($val)?->name ?? $val;
                                        } elseif ($key === 'department_id') {
                                            $oldVal = \App\Models\Department::find($oldVal)?->name ?? '—';
                                            $val = \App\Models\Department::find($val)?->name ?? $val;
                                        }
                                    @endphp
                                    <tr>
                                        <td class="fw-bold text-secondary">{{ $label }}</td>
                                        <td class="text-muted"><del>{{ $oldVal }}</del></td>
                                        <td class="text-success fw-bold">{{ $val }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Actions Form --}}
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-outline-danger px-4 rounded-pill" data-bs-toggle="modal" data-bs-target="#rejectSensitiveModal">
                            <i class="bi bi-x-lg me-1"></i> رفض الطلب
                        </button>
                        <form action="{{ route('affairs.student.process-sensitive-request', $pendingRequest) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" class="btn btn-success px-4 rounded-pill">
                                <i class="bi bi-check-lg me-1"></i> اعتماد وحفظ البيانات الجديدة
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Reject Sensitive Request Modal --}}
            <div class="modal fade" id="rejectSensitiveModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form action="{{ route('affairs.student.process-sensitive-request', $pendingRequest) }}" method="POST" class="modal-content">
                        @csrf
                        <input type="hidden" name="status" value="rejected">
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold text-danger"><i class="bi bi-x-circle-fill me-2"></i>رفض طلب تعديل البيانات الحساسة</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-0">
                                <label class="form-label">سبب الرفض (سيتم عرضه للطالب)</label>
                                <textarea name="rejection_reason" class="form-control" rows="3" placeholder="مثال: يرجى رفع صورة بطاقة الرقم القومي لمطابقة البيانات الجديدة..." required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-danger rounded-pill px-5">تأكيد الرفض</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Documents and Verification Section --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-body p-4">
                <h5 class="fw-bold text-primary mb-3"><i class="bi bi-file-earmark-check me-2"></i>مراجعة وتدقيق المستندات الرسمية</h5>
                <p class="text-muted small">المستندات الخاصة بالطالب والتي تم رفعها عبر البوابة. انقر للاستعراض واعتمادها أو رفضها مع كتابة السبب.</p>
                
                <div class="row g-3">
                    @php
                        $docTypes = [
                            'national_id' => 'بطاقة الرقم القومي',
                            'birth_certificate' => 'شهادة الميلاد الرقمية',
                            'personal_photo' => 'الصورة الشخصية الحديثة',
                            'additional' => 'مستندات إضافية (أخرى)'
                        ];
                    @endphp

                    @foreach($docTypes as $type => $label)
                        @php
                            $doc = $student->documents->where('type', $type)->first();
                        @endphp
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 bg-light bg-opacity-50">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="fw-bold mb-0 text-dark">{{ $label }}</h6>
                                    @if(!$doc)
                                        <span class="badge bg-secondary">غير مرفوع</span>
                                    @elseif($doc->status === 'pending')
                                        <span class="badge bg-warning text-dark">معلق للمراجعة</span>
                                    @elseif($doc->status === 'verified')
                                        <span class="badge bg-success">مقبول ومعتمد</span>
                                    @elseif($doc->status === 'rejected')
                                        <span class="badge bg-danger">مرفوض</span>
                                    @endif
                                </div>

                                @if($doc)
                                    <div class="d-flex align-items-center gap-2 mt-3 mb-2">
                                        <a href="{{ route('document.view', $doc->id) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="bi bi-eye-fill me-1"></i> استعراض الملف
                                        </a>
                                        
                                        @if($doc->status === 'pending')
                                            <form action="{{ route('affairs.student.verify-document', $doc->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="verified">
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                                                    <i class="bi bi-check-circle-fill me-1"></i> قبول
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#rejectDocModal{{ $doc->id }}">
                                                <i class="bi bi-x-circle-fill me-1"></i> رفض
                                            </button>
                                        @endif
                                    </div>

                                    @if($doc->status === 'rejected')
                                        <div class="alert alert-danger p-2 mb-0 mt-2 small">
                                            <strong>سبب الرفض:</strong> {{ $doc->rejection_reason }}
                                        </div>
                                    @endif

                                    {{-- Reject Document Modal --}}
                                    <div class="modal fade" id="rejectDocModal{{ $doc->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <form action="{{ route('affairs.student.verify-document', $doc->id) }}" method="POST" class="modal-content">
                                                @csrf
                                                <input type="hidden" name="status" value="rejected">
                                                <div class="modal-header border-0">
                                                    <h5 class="modal-title fw-bold text-danger"><i class="bi bi-x-circle-fill me-2"></i>رفض المستند: {{ $label }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="mb-0">
                                                        <label class="form-label">سبب الرفض (سيتم عرضه للطالب لإعادة الرفع)</label>
                                                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="مثال: الصورة غير واضحة أو البيانات غير مطابقة..." required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0">
                                                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                                                    <button type="submit" class="btn class btn-danger rounded-pill px-5">تأكيد الرفض</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-muted small mb-0 mt-3"><i class="bi bi-info-circle me-1"></i> لم يتم رفع هذا المستند من قبل الطالب حتى الآن.</p>
                                @endif
							</div>
						</div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Staff-only Internal Notes Section --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-body p-4">
                <h5 class="fw-bold text-primary mb-3"><i class="bi bi-journal-text me-2"></i>سجل الملاحظات الداخلية (للموظفين فقط)</h5>
                
                {{-- Add Note Form --}}
                <form action="{{ route('affairs.student.add-note', $student) }}" method="POST" class="mb-4">
                    @csrf
                    <div class="mb-3">
                        <textarea name="note" class="form-control" rows="2" placeholder="أضف ملاحظة إدارية داخلية حول حالة الطالب..." required></textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn-primary-uni rounded-pill px-4" style="padding: 8px 20px;">
                            <i class="bi bi-plus-circle-fill"></i> حفظ الملاحظة
                        </button>
                    </div>
                </form>

                {{-- Notes List --}}
                <div class="list-group list-group-flush">
                    @forelse($student->internalNotes as $note)
                        <div class="list-group-item px-0 py-3 border-0 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-secondary small"><i class="bi bi-person-fill text-muted me-1"></i>{{ $note->user?->name ?? 'موظف' }}</span>
                                <span class="text-muted small" style="font-size: 0.75rem;">{{ $note->created_at->format('Y-m-d H:i') }}</span>
                            </div>
                            <p class="mb-0 text-dark small">{{ $note->note }}</p>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3 small">
                            <i class="bi bi-chat-left-dots d-block mb-1 text-secondary fs-4"></i>
                            لا توجد ملاحظات داخلية مسجلة لهذا الطالب.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Right Side: Academic Status Management, Status History Timeline --}}
    <div class="col-lg-4">
        {{-- Status Card & Form --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-body p-4">
                <h5 class="fw-bold text-primary mb-3"><i class="bi bi-gear-wide-connected me-2"></i>الحالة الأكاديمية للطالب</h5>
                
                <div class="mb-4 text-center py-2 bg-light rounded-3">
                    <span class="text-muted small d-block">الحالة الحالية:</span>
                    @if($student->status === 'active')
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-4 py-2 mt-1 fs-6">نشط</span>
                    @elseif($student->status === 'suspended')
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-4 py-2 mt-1 fs-6">موقوف</span>
                    @elseif($student->status === 'graduated')
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-4 py-2 mt-1 fs-6">خريج</span>
                    @else
                        <span class="badge bg-secondary px-4 py-2 mt-1 fs-6">غير محدد</span>
                    @endif
                </div>

                {{-- Status Update Form --}}
                <form action="{{ route('affairs.student.update-status', $student) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">تعديل الحالة</label>
                        <select name="status" class="form-select" required>
                            <option value="active" {{ $student->status === 'active' ? 'selected' : '' }}>نشط</option>
                            <option value="suspended" {{ $student->status === 'suspended' ? 'selected' : '' }}>موقوف</option>
                            <option value="graduated" {{ $student->status === 'graduated' ? 'selected' : '' }}>خريج</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">سبب و تفاصيل التعديل (إلزامي)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="اكتب سبباً إدارياً لتغيير الحالة..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold" style="background:var(--primary); border: none;">
                        <i class="bi bi-save-fill me-1"></i> حفظ وتغيير الحالة
                    </button>
                </form>
            </div>
        </div>

        {{-- Status Change History Timeline --}}
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-body p-4">
                <h5 class="fw-bold text-primary mb-3"><i class="bi bi-clock-history me-2"></i>سجل الحالات السابقة</h5>
                
                <div class="timeline position-relative ps-3" style="border-right: 2px solid #e2e8f0; margin-right: 5px;">
                    @forelse($student->statusHistories as $history)
                        <div class="timeline-item position-relative mb-4">
                            {{-- Bullet --}}
                            <div class="position-absolute rounded-circle bg-white border border-primary" style="width: 12px; height: 12px; right: -21px; top: 5px;"></div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                @if($history->status === 'active')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-0.5" style="font-size:0.75rem;">نشط</span>
                                @elseif($history->status === 'suspended')
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-0.5" style="font-size:0.75rem;">موقوف</span>
                                @elseif($history->status === 'graduated')
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-0.5" style="font-size:0.75rem;">خريج</span>
                                @endif
                                <span class="text-muted small" style="font-size: 0.72rem;">{{ $history->created_at->format('Y-m-d H:i') }}</span>
                            </div>
                            
                            <p class="mb-1 text-dark small" style="line-height: 1.5;">{{ $history->notes }}</p>
                            <span class="text-muted small d-block" style="font-size: 0.75rem;"><i class="bi bi-person-fill me-1"></i>الموظف: {{ $history->changer?->name ?? 'غير محدد' }}</span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3 small">
                            لا يوجد سجل سابق لتغيير الحالة.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
