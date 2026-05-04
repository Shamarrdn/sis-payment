@extends('layouts.app')

@section('title', 'الإعدادات المالية')
@section('page-heading', 'الإعدادات المالية وإدارة الصلاحيات')
@section('user-name', auth()->user()->name)

@section('user-name', auth()->user()->name)

@section('content')

@if(session('success'))
    <div class="alert alert-success mb-4"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger mb-4">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

{{-- TAB NAV --}}
<ul class="nav nav-pills mb-4 gap-2" id="settingsTabs">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="pill" href="#tab-settings">
            <i class="bi bi-sliders me-1"></i> الإعدادات المالية
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="pill" href="#tab-discounts">
            <i class="bi bi-percent me-1"></i> الإعفاءات والمنح
        </a>
    </li>
</ul>

<div class="tab-content">

    {{-- ─── Tab 1: System Settings ─── --}}
    <div class="tab-pane fade show active" id="tab-settings">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold mb-0"><i class="bi bi-sliders me-2"></i>الإعدادات المالية (من قاعدة البيانات)</h5>
                <p class="text-muted small mt-1">تعديل هذه القيم يؤثر على حسابات الدفع مباشرة.</p>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf

                    @forelse($settings as $group => $groupSettings)
                        <h6 class="text-primary fw-bold border-bottom pb-2 mb-3 mt-4">
                            @if($group === 'tuition') <i class="bi bi-cash-stack me-1"></i> المصاريف الدراسية
                            @elseif($group === 'payment') <i class="bi bi-credit-card me-1"></i> إعدادات الدفع
                            @else <i class="bi bi-gear me-1"></i> إعدادات عامة
                            @endif
                        </h6>
                        <div class="row g-3 mb-2">
                            @foreach($groupSettings as $setting)
                            <div class="col-md-6">
                                <label class="form-label fw-bold">{{ $setting->label ?? $setting->key }}</label>
                                @if($setting->type === 'boolean')
                                    <div class="form-check form-switch mt-1">
                                        <input class="form-check-input" type="checkbox"
                                               name="settings[{{ $setting->key }}][value]"
                                               value="1"
                                               {{ $setting->value ? 'checked' : '' }}>
                                    </div>
                                @else
                                    <input type="{{ $setting->type === 'integer' ? 'number' : 'text' }}"
                                           name="settings[{{ $setting->key }}][value]"
                                           class="form-control"
                                           value="{{ $setting->value }}">
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @empty
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle me-2"></i>
                            لا توجد إعدادات مضافة بعد. شغّل السـيدر لتحميل الإعدادات الافتراضية:
                            <code class="ms-2">php artisan db:seed --class=SystemSettingsSeeder</code>
                        </div>
                    @endforelse

                    <div class="mt-4">
                        <button class="btn btn-primary px-5"><i class="bi bi-save me-2"></i>حفظ الإعدادات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ─── Tab 2: Discounts ─── --}}
    <div class="tab-pane fade" id="tab-discounts">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">الإعفاءات والمنح الدراسية</h5>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDiscountModal">
                <i class="bi bi-plus-lg me-1"></i> إضافة إعفاء
            </button>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">اسم الإعفاء</th>
                            <th>فئة الطالب</th>
                            <th>النوع</th>
                            <th>القيمة</th>
                            <th>جهة الاعتماد</th>
                            <th>الحالة</th>
                            <th class="pe-4">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($discounts as $discount)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $discount->name }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $discount->category }}</span></td>
                            <td>
                                @if($discount->type === 'full') <span class="badge bg-success">إعفاء كامل</span>
                                @elseif($discount->type === 'percentage') <span class="badge bg-info text-dark">نسبة %</span>
                                @else <span class="badge bg-warning text-dark">مبلغ ثابت</span>
                                @endif
                            </td>
                            <td class="fw-bold">
                                @if($discount->type === 'full') — مجاناً
                                @elseif($discount->type === 'percentage') {{ $discount->value }}%
                                @else {{ number_format($discount->value) }} ج.م
                                @endif
                            </td>
                            <td class="text-muted small">{{ $discount->approving_authority ?? '—' }}</td>
                            <td>
                                @if($discount->is_active) <span class="badge bg-success">مفعّل</span>
                                @else <span class="badge bg-secondary">معطّل</span>
                                @endif
                            </td>
                            <td class="pe-4">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editDiscount{{ $discount->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.discounts.delete', $discount) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('حذف هذا الإعفاء؟')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                {{-- Edit Modal --}}
                                <div class="modal fade" id="editDiscount{{ $discount->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <form action="{{ route('admin.discounts.update', $discount) }}" method="POST" class="modal-content">
                                            @csrf @method('PUT')
                                            <div class="modal-header border-0"><h5 class="modal-title fw-bold">تعديل الإعفاء</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <div class="modal-body p-4">
                                                @include('admin.settings._discount_form', ['d' => $discount])
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">إلغاء</button>
                                                <button type="submit" class="btn btn-primary px-5">حفظ</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center p-5 text-muted"><i class="bi bi-percent fs-1 d-block mb-3"></i>لا توجد إعفاءات مضافة بعد.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add Discount Modal --}}
<div class="modal fade" id="addDiscountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.discounts.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header border-0"><h5 class="modal-title fw-bold">إضافة إعفاء / منحة جديدة</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">
                @include('admin.settings._discount_form', ['d' => null])
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-primary px-5">إضافة</button>
            </div>
        </form>
    </div>
</div>
@endsection
