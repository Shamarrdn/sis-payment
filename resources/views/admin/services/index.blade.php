@extends('layouts.app')

@section('title', 'إدارة الخدمات')
@section('page-heading', 'إدارة الخدمات والأسعار')
@section('user-name', auth()->user()->name)

@section('user-name', auth()->user()->name)

@section('content')
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="page-title">إدارة الخدمات والرسوم</h2>
            <p class="page-subtitle mb-0">تحكم كامل في مسميات الخدمات والأسعار الرسمية للجامعة</p>
        </div>
        @if(in_array(auth()->user()->role, ['super_admin', 'financial_affairs']))
        <button class="btn-primary-uni" data-bs-toggle="modal" data-bs-target="#addServiceModal">
            <i class="bi bi-plus-lg"></i> إضافة خدمة جديدة
        </button>
        @endif
    </div>

    {{-- Category Filters --}}
    <div class="mb-5">
        <div class="d-flex gap-2 flex-wrap" id="category-filters">
            <button class="btn btn-category active" data-category="all">
                <i class="bi bi-grid-fill me-2"></i>كل الخدمات
            </button>
            @php
                $types = $services->pluck('type')->unique();
                $iconMap = [
                    'خدمات عامة' => ['icon' => 'bi-collection-fill', 'color' => '#3b82f6'],
                    'شئون طلاب' => ['icon' => 'bi-person-badge-fill', 'color' => '#8b5cf6'],
                    'شئون طلبة' => ['icon' => 'bi-person-badge-fill', 'color' => '#8b5cf6'],
                    'التماسات' => ['icon' => 'bi-file-earmark-text-fill', 'color' => '#f59e0b'],
                    'سمر كورس' => ['icon' => 'bi-sun-fill', 'color' => '#ef4444'],
                    'مصاريف دراسية' => ['icon' => 'bi-cash-stack', 'color' => '#10b981'],
                    'مصروفات دراسية' => ['icon' => 'bi-cash-stack', 'color' => '#10b981'],
                    'خريجين' => ['icon' => 'bi-mortarboard-fill', 'color' => '#ec4899']
                ];
            @endphp
            @foreach($types as $type)
                <button class="btn btn-category" data-category="{{ Str::slug($type, '-') }}">
                    <i class="bi {{ $iconMap[$type]['icon'] ?? 'bi-circle-fill' }} me-2"></i>{{ $type }}
                </button>
            @endforeach
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4 d-flex align-items-center" style="border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: #065f46;">
            <i class="bi bi-check-circle-fill me-3 fs-4"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius: 12px; background: rgba(239, 68, 68, 0.1); color: #991b1b;">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                <div class="fw-bold">حدث خطأ أثناء تنفيذ العملية:</div>
            </div>
            <ul class="mb-0 ps-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div id="services-container">
        @php $groupedServices = $services->groupBy('type'); @endphp
        
        @foreach($groupedServices as $type => $servicesInGroup)
            <div class="service-section mb-5" data-category="{{ Str::slug($type, '-') }}">
                <h5 class="fw-bold mb-4 d-flex align-items-center" style="color: var(--primary);">
                    <div class="rounded-pill p-2 me-2 d-flex align-items-center justify-content-center" 
                         style="background: {{ ($iconMap[$type]['color'] ?? '#eee') . '20' }}; color: {{ $iconMap[$type]['color'] ?? '#666' }};">
                        <i class="bi {{ $iconMap[$type]['icon'] ?? 'bi-tag' }}"></i>
                    </div>
                    {{ $type }}
                    <span class="badge ms-2 rounded-pill font-monospace" style="background: #f1f5f9; color: #64748b; font-size: 0.7rem;">{{ $servicesInGroup->count() }}</span>
                </h5>
                
                <div class="row g-4">
                    @foreach($servicesInGroup as $service)
                        <div class="col-xl-4 col-md-6 service-card-wrapper">
                            <div class="stat-card h-100 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge rounded-pill px-3 py-2" 
                                          style="background: var(--accent-soft); color: var(--accent); font-weight: 800; border: 1px solid rgba(212, 175, 55, 0.3);">
                                        {{ $service->type }}
                                    </span>
                                    @if(in_array(auth()->user()->role, ['super_admin', 'financial_affairs']))
                                    <div class="dropdown">
                                        <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                            <li>
                                                <button class="dropdown-item py-2" data-bs-toggle="modal" data-bs-target="#editServiceModal{{ $service->id }}">
                                                    <i class="bi bi-pencil me-2 text-primary"></i> تعديل الخدمة
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                    @endif
                                </div>
                                
                                <h5 class="fw-bold mb-2" style="color: var(--primary); line-height: 1.4;">
                                    {{ $service->name }}
                                    @if(!$service->is_active)
                                        <span class="badge bg-danger rounded-pill ms-2" style="font-size: 0.7rem;">متوقفة</span>
                                    @endif
                                </h5>
                                
                                <div class="d-flex flex-wrap gap-1 mb-3">
                                    @if($service->faculty)
                                        <span class="badge bg-light text-primary border"><i class="bi bi-building me-1"></i>{{ $service->faculty->code }}</span>
                                    @else
                                        <span class="badge bg-light text-muted border">كل الكليات</span>
                                    @endif
                                    
                                    @if($service->applicable_to)
                                        <span class="badge bg-light text-dark border"><i class="bi bi-person-check me-1"></i>{{ $service->applicable_to }} فقط</span>
                                    @endif
                                </div>
                                
                                <div class="mt-auto pt-4 d-flex align-items-center justify-content-between border-top">
                                    <div class="price-tag">
                                        <div class="small text-muted mb-1">السعر الحالي</div>
                                        <div class="h4 fw-bold mb-0" style="color: var(--primary);">
                                            {{ number_format($service->amount) }} 
                                            <span class="small fw-normal">ج.م</span>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <form action="{{ route('admin.services.toggle', $service->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-sm px-3 rounded-pill {{ $service->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}" style="font-weight: 700;">
                                                <i class="bi {{ $service->is_active ? 'bi-pause-circle-fill' : 'bi-play-circle-fill' }} me-1"></i>
                                                {{ $service->is_active ? 'إيقاف' : 'تفعيل' }}
                                            </button>
                                        </form>
                                        
                                        @if(in_array(auth()->user()->role, ['super_admin', 'financial_affairs']))
                                        <button class="btn btn-sm px-3 rounded-pill" 
                                                style="background: #f8fafc; border: 1.5px solid #e2e8f0; font-weight: 700; color: var(--primary);"
                                                data-bs-toggle="modal" data-bs-target="#editServiceModal{{ $service->id }}">
                                            تعديل
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Service Modal -->
                            <div class="modal fade" id="editServiceModal{{ $service->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <form action="{{ route('admin.services.update', $service->id) }}" method="POST" class="modal-content">
                                        @csrf @method('PUT')
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold">تحديث بيانات الخدمة</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="mb-4">
                                                <label class="form-label">مسمى الخدمة</label>
                                                <input type="text" name="name" class="form-control form-control-lg" value="{{ old('name', $service->name) }}" required>
                                            </div>
                                            <div class="mb-4">
                                                <label class="form-label">التصنيف</label>
                                                <select name="type" class="form-select form-select-lg" required>
                                                    <option value="خدمات عامة" {{ $service->type == 'خدمات عامة' ? 'selected' : '' }}>خدمات عامة</option>
                                                    <option value="شئون طلبة" {{ in_array($service->type, ['شئون طلاب', 'شئون طلبة']) ? 'selected' : '' }}>شئون طلبة</option>
                                                    <option value="خريجين" {{ $service->type == 'خريجين' ? 'selected' : '' }}>خريجين</option>
                                                    <option value="التماسات" {{ $service->type == 'التماسات' ? 'selected' : '' }}>التماسات</option>
                                                    <option value="سمر كورس" {{ $service->type == 'سمر كورس' ? 'selected' : '' }}>سمر كورس</option>
                                                    <option value="مصروفات دراسية" {{ in_array($service->type, ['مصاريف دراسية', 'مصروفات دراسية']) ? 'selected' : '' }}>مصروفات دراسية</option>
                                                </select>
                                            </div>
                                            @include('admin.services._extra_fields', ['service' => $service])
                                            <div class="row g-3 mb-4">
                                                <div class="col-md-6">
                                                    <label class="form-label">السعر (ج.م)</label>
                                                    <div class="input-group">
                                                        <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount', $service->amount) }}" required min="0">
                                                        <span class="input-group-text bg-light fw-bold">ج.م</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">الكلية (اختياري)</label>
                                                    <select name="faculty_id" class="form-select">
                                                        <option value="">كل الكليات</option>
                                                        @foreach($faculties as $f)
                                                            <option value="{{ $f->id }}" {{ $service->faculty_id == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">فئة الطلاب المسموح لهم (اختياري)</label>
                                                    <select name="applicable_to" class="form-select">
                                                        <option value="">كل الفئات</option>
                                                        <option value="Student" {{ $service->applicable_to == 'Student' ? 'selected' : '' }}>طلاب فقط</option>
                                                        <option value="Graduate" {{ $service->applicable_to == 'Graduate' ? 'selected' : '' }}>خريجين فقط</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mt-4 mb-3 d-flex gap-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="is_active" id="isActive{{ $service->id }}" {{ $service->is_active ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="isActive{{ $service->id }}">تفعيل الخدمة</label>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="allows_quantity" id="allowQuantity{{ $service->id }}" {{ $service->allows_quantity ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="allowQuantity{{ $service->id }}">تفعيل الكمية المتعددة</label>
                                                </div>
                                            </div>
                                            <div class="form-text mt-3 text-primary small bg-light p-3 rounded" style="border-right: 4px solid var(--accent);">
                                                <i class="bi bi-info-circle-fill me-1"></i>
                                                تأكد من مراجعة السعر والحالة، أي تغيير سيتم تحديثه فوراً في حسابات الطلاب.
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 pt-0">
                                            <button type="button" class="btn btn-light px-4 rounded-pill fw-bold" data-bs-dismiss="modal">إلغاء</button>
                                            <button type="submit" class="btn-primary-uni rounded-pill px-5">حفظ التغييرات</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <!-- Add Service Modal -->
    <div class="modal fade" id="addServiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('admin.services.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">إضافة خدمة جامعية جديدة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label">اسم الخدمة</label>
                        <input type="text" name="name" class="form-control form-control-lg" placeholder="مثلاً: استخراج شهادة تخرج" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">التصنيف</label>
                        <select name="type" class="form-select form-select-lg" required>
                            <option value="خدمات عامة" selected>خدمات عامة</option>
                            <option value="شئون طلبة">شئون طلبة</option>
                            <option value="خريجين">خريجين</option>
                            <option value="التماسات">التماسات</option>
                            <option value="سمر كورس">سمر كورس</option>
                            <option value="مصروفات دراسية">مصروفات دراسية</option>
                        </select>
                    </div>
                    @include('admin.services._extra_fields')
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">السعر الافتراضي (ج.م)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required min="0">
                                <span class="input-group-text bg-light fw-bold">ج.م</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الكلية (اختياري)</label>
                            <select name="faculty_id" class="form-select">
                                <option value="">كل الكليات</option>
                                @foreach($faculties as $f)
                                    <option value="{{ $f->id }}">{{ $f->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">فئة الطلاب (اختياري)</label>
                            <select name="applicable_to" class="form-select">
                                <option value="">كل الفئات</option>
                                <option value="Student">طلاب فقط</option>
                                <option value="Graduate">خريجين فقط</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 mb-3 d-flex gap-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="newIsActive" checked>
                            <label class="form-check-label" for="newIsActive">تفعيل الخدمة مبدئياً</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="allows_quantity" id="newAllowQuantity">
                            <label class="form-check-label" for="newAllowQuantity">تفعيل الكمية المتعددة</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light px-4 rounded-pill fw-bold" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn-primary-uni rounded-pill px-5">إنشاء الخدمة</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('extra-styles')
<style>
    .btn-category {
        background: #fff;
        border: 1.5px solid #e2e8f0;
        border-radius: 50px;
        padding: 10px 24px;
        font-weight: 700;
        color: var(--primary);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: var(--shadow-sm);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-category:hover {
        background: #f8fafc;
        border-color: var(--accent);
        transform: translateY(-1px);
    }
    .btn-category.active {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.15);
    }
    .service-section {
        transition: all 0.4s ease;
    }
    .service-section.hidden {
        display: none;
        opacity: 0;
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categoryBtns = document.querySelectorAll('.btn-category');
        const sections = document.querySelectorAll('.service-section');

        categoryBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Update active state
                categoryBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const category = btn.getAttribute('data-category');

                sections.forEach(section => {
                    if (category === 'all' || section.getAttribute('data-category') === category) {
                        section.classList.remove('hidden');
                        setTimeout(() => section.style.opacity = '1', 10);
                    } else {
                        section.style.opacity = '0';
                        section.classList.add('hidden');
                    }
                });
            });
        });
    });
</script>
@endsection
