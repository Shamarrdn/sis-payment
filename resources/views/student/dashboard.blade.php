<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>داشبورد الطالب</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1a2d5a; --accent: #c8a96e; --sidebar-w: 260px; }
        body { font-family: 'Tajawal', sans-serif; background: #f0f4f8; }

        .sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: linear-gradient(180deg, var(--primary) 0%, #233872 100%);
            position: fixed; top: 0; right: 0;
            display: flex; flex-direction: column;
            box-shadow: -4px 0 20px rgba(0,0,0,0.15);
            z-index: 100;
        }
        .sidebar-logo { padding: 20px 16px; background: rgba(0,0,0,0.2); text-align: center; }
        .sidebar-logo img { width: 60px; height: 60px; border-radius: 50%; border: 3px solid var(--accent); object-fit: cover; }
        .sidebar-logo h6 { color: #fff; font-weight: 700; margin-top: 8px; font-size: 0.85rem; }
        .sidebar-logo small { color: var(--accent); font-size: 0.78rem; }
        .sidebar-nav { flex: 1; padding: 16px 12px; }
        .sidebar-nav .nav-label { font-size: 0.7rem; font-weight: 700; color: rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 0.1em; padding: 12px 12px 6px; display: block; }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px; border-radius: 10px;
            color: rgba(255,255,255,0.8); text-decoration: none;
            font-size: 0.92rem; font-weight: 500; margin-bottom: 4px;
            transition: all 0.2s;
        }
        .sidebar-nav a.active { background: rgba(200,169,110,0.25); color: var(--accent); border-right: 3px solid var(--accent); }
        .sidebar-footer { padding: 14px 12px; border-top: 1px solid rgba(255,255,255,0.1); }
        .sidebar-footer form button {
            width: 100%; background: rgba(220,53,69,0.15); color: #ff6b7a;
            border: 1px solid rgba(220,53,69,0.3); border-radius: 10px; padding: 10px;
            font-family: 'Tajawal',sans-serif; font-size: 0.88rem; font-weight: 600;
            display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer;
            transition: all 0.2s;
        }

        .main-content { margin-right: var(--sidebar-w); }
        .top-header {
            background: #fff; border-bottom: 1px solid #e5eaf2; padding: 14px 30px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 50; box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }
        .top-header h5 { font-weight: 700; color: var(--primary); margin: 0; }
        .user-badge {
            display: flex; align-items: center; gap: 10px;
            background: #f0f4f8; padding: 6px 14px 6px 6px; border-radius: 50px;
            font-size: 0.88rem; font-weight: 600; color: var(--primary);
        }
        .user-badge .avatar { width: 34px; height: 34px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; }
        .user-badge.dropdown-toggle::after { display: none; }
        .dropdown-item:active { background-color: var(--primary); }
        .dropdown-item i { transition: transform 0.2s; }
        .dropdown-item:hover i { transform: scale(1.1); }
        .content-area { padding: 30px; }

        .service-card {
            background: #fff; border-radius: 16px; border: 1.5px solid #e8eef6;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
            transition: all 0.2s; overflow: hidden;
        }
        .service-card:hover { transform: translateY(-4px); box-shadow: 0 10px 28px rgba(26,45,90,0.12); border-color: var(--primary); }
        .service-card .card-top { padding: 24px; text-align: center; }
        .service-card .svc-icon { width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; font-size: 1.7rem; }
        .service-card h6 { font-weight: 700; font-size: 0.95rem; color: #1a2d5a; min-height: 36px; }
        .service-card .price { font-size: 1.6rem; font-weight: 800; color: #2e7d32; }
        .service-card .btn-pay {
            display: block; background: var(--primary); color: #fff;
            text-align: center; padding: 12px; font-family: 'Tajawal',sans-serif;
            font-weight: 700; text-decoration: none; border-top: 1px solid #e8eef6;
            transition: all 0.2s;
        }
        .service-card .btn-pay:hover { background: #eef2f6 !important; color: var(--primary) !important; text-decoration: none; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-logo">
        <img src="{{ asset('images/uni.jpg') }}" alt="">
        <h6>{{ auth()->guard('student')->user()->name }}</h6>
        <small style="color:var(--accent);font-size:0.75rem;line-height:1.6;display:block;">
            {{ auth()->guard('student')->user()->facultyName() }}
        </small>
        <small style="color:rgba(255,255,255,0.65);font-size:0.72rem;">
            {{ auth()->guard('student')->user()->academic_year }}
            @if(auth()->guard('student')->user()->departmentName() !== '—')
                &nbsp;|&nbsp;{{ auth()->guard('student')->user()->departmentName() }}
            @endif
        </small>
    </div>
    <nav class="sidebar-nav">
        @include('student.partials.sidebar-nav')
    </nav>
    <div class="sidebar-footer">
        <form action="{{ route('student.logout') }}" method="POST">
            @csrf
            <button type="submit"><i class="bi bi-box-arrow-right"></i> تسجيل خروج</button>
        </form>
    </div>
</div>

<div class="main-content">
    <div class="top-header">
        <h5>بوابة الطالب الإلكترونية</h5>
        <div class="dropdown">
            <div class="user-badge dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                <div class="avatar"><i class="bi bi-mortarboard-fill"></i></div>
                {{ auth()->guard('student')->user()->name }}
            </div>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2" aria-labelledby="profileDropdown" style="border-radius: 12px; min-width: 220px; padding: 8px;">
                <li><h6 class="dropdown-header mb-2" style="font-weight: 700; color: #7a8aaa;">خيارات الحساب</h6></li>
                <li>
                    <a class="dropdown-item rounded-3 py-2" href="{{ route('student.profile') }}">
                        <i class="bi bi-person-badge-fill me-2 text-primary"></i> تكملة بياناتي
                    </a>
                </li>
                <li><hr class="dropdown-divider mx-2"></li>
                <li>
                    <form action="{{ route('student.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item rounded-3 py-2 text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i> تسجيل الخروج
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <div class="content-area">
        @if(session('success'))
            <div class="alert alert-success rounded-3 mb-4"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
        @endif

        @if(isset($unpaidCount) && $unpaidCount > 0)
            <div class="alert alert-warning rounded-3 mb-4 d-flex justify-content-between align-items-center">
                <div><i class="bi bi-exclamation-triangle-fill me-2"></i> لديك <strong>{{ $unpaidCount }}</strong> عملية دفع قيد الانتظار لم تكتمل بعد.</div>
                <a href="{{ route('student.history') }}" class="btn btn-sm btn-outline-dark">عرض الأرشيف</a>
            </div>
        @endif

        @if(isset($announcements) && $announcements->isNotEmpty())
            <div class="card border-0 shadow-sm mb-4" style="border-right:4px solid var(--accent)!important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0"><i class="bi bi-megaphone-fill text-warning me-2"></i>إعلانات</h6>
                        <a href="{{ route('student.announcements') }}" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                    </div>
                    @foreach($announcements as $ann)
                        <div class="mb-2 pb-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <strong>{{ $ann->title }}</strong>
                            <p class="text-muted small mb-0">{{ Str::limit($ann->content, 100) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if(isset($unreadNotifications) && $unreadNotifications > 0)
            <div class="alert alert-info rounded-3 mb-4 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bell-fill me-2"></i> لديك {{ $unreadNotifications }} إشعار غير مقروء</span>
                <a href="{{ route('student.notifications') }}" class="btn btn-sm btn-primary">عرض</a>
            </div>
        @endif

        {{-- ─── Student Info Card ─── --}}
        @php $authStudent = auth()->guard('student')->user(); @endphp
        <div class="mb-4 p-4 rounded-4 d-flex align-items-center gap-4 flex-wrap"
             style="background:linear-gradient(135deg,#1a2d5a 0%,#233872 100%);color:#fff;box-shadow:0 8px 24px rgba(26,45,90,0.18);">
            <div class="d-flex align-items-center gap-3">
                <div style="width:54px;height:54px;background:rgba(200,169,110,0.25);border:2px solid var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">
                    <i class="bi bi-mortarboard-fill" style="color:var(--accent);"></i>
                </div>
                <div>
                    <div style="font-weight:800;font-size:1.05rem;">{{ $authStudent->name }}</div>
                    <div style="font-size:0.78rem;opacity:0.7;">{{ $authStudent->national_id }}</div>
                </div>
            </div>
            <div style="width:1px;height:44px;background:rgba(255,255,255,0.2);"></div>
            <div class="d-flex gap-4 flex-wrap">
                <div>
                    <div style="font-size:0.7rem;opacity:0.65;text-transform:uppercase;letter-spacing:.05em;">الكلية</div>
                    <div style="font-weight:700;font-size:0.92rem;color:var(--accent);">{{ $authStudent->facultyName() }}</div>
                </div>
                <div>
                    <div style="font-size:0.7rem;opacity:0.65;text-transform:uppercase;letter-spacing:.05em;">الفرقة</div>
                    <div style="font-weight:700;font-size:0.92rem;">{{ $authStudent->academic_year ?? '—' }}</div>
                </div>
                @if($authStudent->departmentName() !== '—')
                <div>
                    <div style="font-size:0.7rem;opacity:0.65;text-transform:uppercase;letter-spacing:.05em;">القسم</div>
                    <div style="font-weight:700;font-size:0.92rem;">{{ $authStudent->departmentName() }}</div>
                </div>
                @endif
                @if($authStudent->program)
                <div>
                    <div style="font-size:0.7rem;opacity:0.65;text-transform:uppercase;letter-spacing:.05em;">التخصص</div>
                    <div style="font-weight:700;font-size:0.92rem;">{{ $authStudent->program }}</div>
                </div>
                @endif
            </div>
        </div>

        <div class="mb-4">
            <h2 style="font-size:1.6rem;font-weight:800;color:#1a2d5a;">الخدمات والرسوم الإدارية</h2>
            <p class="text-muted">اختر الخدمة المطلوبة للمتابعة والسداد عبر بوابة الجامعة الإلكترونية</p>
        </div>

        {{-- ─── المصاريف الدراسية (بطاقة خاصة) ─── --}}
        <div class="mb-5">
            <h5 class="fw-bold mb-3" style="color:#1a2d5a;">
                <i class="bi bi-bank me-2"></i>المصاريف الدراسية
            </h5>
            <div class="rounded-4 p-4" style="background:#fff;border:2px solid #e5eaf2;color:var(--primary);box-shadow:0 4px 20px rgba(0,0,0,0.04);position:relative;overflow:hidden;">
                <!-- Decorative element -->
                <div style="position:absolute;top:0;left:0;width:100%;height:4px;background:var(--accent);"></div>
                
                <div class="row align-items-center g-4">
                    <div class="col-md-6">
                        <div style="font-size:0.85rem;color:#64748b;margin-bottom:4px;font-weight:600;">إجمالي المصاريف الدراسية المقررة</div>
                        <div style="font-size:2.4rem;font-weight:800;color:var(--primary);">{{ number_format($resolution['total']) }} <small style="font-size:1.1rem;">ج.م</small></div>
                        <div style="font-size:0.85rem;color:#64748b;margin-top:2px;">
                            القسط الأول: <span class="fw-bold text-dark">{{ number_format($resolution['inst1_amount']) }} ج.م</span>
                            &nbsp;|&nbsp;
                            القسط الثاني: <span class="fw-bold text-dark">{{ number_format($resolution['inst2_amount']) }} ج.م</span>
                        </div>
                        <div class="d-flex gap-2 mt-3 flex-wrap">
                            @if($paidFull)
                                <span class="badge rounded-pill px-3 py-2 bg-success bg-opacity-10 text-success border border-success"><i class="bi bi-check-circle-fill me-1"></i> مسددة بالكامل</span>
                            @elseif($paidInst1 && $paidInst2)
                                <span class="badge rounded-pill px-3 py-2 bg-success bg-opacity-10 text-success border border-success"><i class="bi bi-check-circle-fill me-1"></i> مسددة (قسطين)</span>
                            @elseif($paidInst1)
                                <span class="badge rounded-pill px-3 py-2 bg-success bg-opacity-10 text-success border border-success"><i class="bi bi-check-circle-fill me-1"></i> تم سداد القسط الأول</span>
                                <span class="badge rounded-pill px-3 py-2 bg-light text-dark border"><i class="bi bi-clock me-1"></i> القسط الثاني متبقي: {{ number_format($resolution['inst2_amount']) }} ج.م</span>
                            @else
                                <span class="badge rounded-pill px-3 py-2 bg-light text-dark border"><i class="bi bi-hourglass me-1"></i> لم يُسدد بعد</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end gap-3 flex-wrap">
                        @if(!$paidFull && !($paidInst1 && $paidInst2))
                            <a href="{{ route('student.tuition') }}"
                               class="btn btn-primary px-4 py-2" style="background:var(--primary);border-radius:8px;font-weight:700;">
                                <i class="bi bi-credit-card me-2"></i> {{ $paidInst1 ? 'سداد القسط الثاني' : 'متابعة السداد' }}
                            </a>
                        @else
                            <span class="badge bg-success px-4 py-3 rounded-3 fs-6 d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill"></i> مكتملة
                            </span>
                        @endif
                    </div>
                </div>
                {{-- Progress bar --}}
                @php
                    $progress = $paidFull || ($paidInst1 && $paidInst2) ? 100 : ($paidInst1 ? 66 : 0);
                    $progressColor = $progress === 100 ? '#10b981' : 'var(--accent)';
                @endphp
                <div class="mt-4 pt-3 border-top">
                    <div class="d-flex justify-content-between mb-2" style="font-size:0.8rem;color:#64748b;font-weight:600;">
                        <span>نسبة السداد</span><span>{{ $progress }}%</span>
                    </div>
                    <div style="background:#e2e8f0;border-radius:50px;height:6px;">
                        <div style="background:{{ $progressColor }};border-radius:50px;height:6px;width:{{ $progress }}%;transition:width 0.5s;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── Most Used Services ─── --}}
        @if($mostUsed->count() > 0)
        <div class="mb-5">
            <h5 class="fw-bold mb-3" style="color:#1a2d5a;">
                <i class="bi bi-bookmark-star-fill me-2" style="color:var(--accent);"></i>الخدمات الشائعة
            </h5>
            <div class="row g-3">
                @foreach($mostUsed as $service)
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="service-card h-100" style="border-color: #e5eaf2; background: #fff;">
                            <div class="card-top p-4 text-center">
                                <div class="svc-icon bg-light text-primary" style="width:50px;height:50px;margin:0 auto 12px;font-size:1.3rem;">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                                <h6 style="font-size: 0.95rem; font-weight: 700; color: var(--primary);">{{ $service->name }}</h6>
                                <div class="mt-2" style="font-size: 1.2rem; font-weight: 800; color: #10b981;">{{ number_format($service->amount) }} <small style="font-size:0.8rem;color:#64748b;font-weight:500;">ج.م</small></div>
                            </div>
                            <a href="{{ route('student.checkout', $service) }}" class="btn-pay" style="background: #f8fafc; color: var(--primary); border-top: 1px solid #e5eaf2;">
                                <i class="bi bi-credit-card me-2"></i> سداد الرسوم
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ─── Service Categories ─── --}}
        <div class="mb-4">
            <h5 class="fw-bold mb-3" style="color:#1a2d5a;">دليل الخدمات والتصنيفات</h5>
            <div class="d-flex gap-2 flex-wrap mb-4">
                <button class="btn btn-category active" data-category="all">
                    الكل
                </button>
                @foreach($services as $type => $group)
                    <button class="btn btn-category" data-category="{{ Str::slug($type, '-') }}">
                        {{ \App\Support\ServiceCategoryGroups::label($type) }}
                    </button>
                @endforeach
            </div>
        </div>

        <div id="services-grid">
            @foreach($services as $type => $group)
                <div class="service-section mb-2" data-type="{{ Str::slug($type, '-') }}">
                    <h6 class="fw-bold mb-3 section-title" style="color:#64748b; font-size: 0.9rem;">
                        {{ \App\Support\ServiceCategoryGroups::label($type) }}
                    </h6>
                    <div class="row g-3 mb-4">
                        @foreach($group as $service)
                            <div class="col-xl-3 col-lg-4 col-md-6 service-item">
                                <div class="service-card h-100" style="border: 1px solid #e5eaf2; border-radius: 12px;">
                                    <div class="card-top p-4 text-center">
                                        <h6 style="font-size: 0.95rem; font-weight: 700; color: var(--primary);">{{ $service->name }}</h6>
                                        @if($service->estimated_days)
                                            <small class="text-muted d-block">~{{ $service->estimated_days }} أيام عمل</small>
                                        @endif
                                        <div class="mt-2" style="font-size: 1.2rem; font-weight: 800; color: #10b981;">{{ number_format($service->amount) }} <small style="font-size:0.8rem;color:#64748b;font-weight:500;">ج.م</small></div>
                                    </div>
                                    <a href="{{ route('student.checkout', $service) }}" class="btn-pay" style="background: #f8fafc; color: var(--primary); border-top: 1px solid #e5eaf2; padding: 10px; font-size: 0.9rem;">
                                        متابعة السداد
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
    </div>

        {{-- ─── Recent Payments ─── --}}
        @if(isset($recentPayments) && $recentPayments->count() > 0)
        <div class="mt-5 mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0" style="color:#1a2d5a;">
                    <i class="bi bi-clock-history me-2" style="color:#3b82f6;"></i>أحدث المدفوعات
                </h5>
                <a href="{{ route('student.history') }}" class="btn btn-sm btn-outline-primary rounded-pill">عرض الأرشيف الكامل</a>
            </div>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:0.9rem;">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th class="text-secondary fw-semibold py-3 px-4">رقم العملية</th>
                                <th class="text-secondary fw-semibold py-3">الخدمة</th>
                                <th class="text-secondary fw-semibold py-3">التاريخ</th>
                                <th class="text-secondary fw-semibold py-3">المبلغ</th>
                                <th class="text-secondary fw-semibold py-3">الحالة</th>
                                <th class="text-secondary fw-semibold py-3 text-center">الإيصال</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPayments as $payment)
                            <tr>
                                <td class="px-4"><span class="badge bg-light text-dark border">{{ $payment->transaction_id ?? 'N/A' }}</span></td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $payment->service->name ?? $payment->notes }}</div>
                                    @if($payment->subject)
                                        <div class="small text-muted">{{ $payment->subject }}</div>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d h:i A') }}</td>
                                <td><span class="fw-bold" style="color:var(--primary);">{{ number_format($payment->total_amount) }} ج.م</span></td>
                                <td>
                                    @if($payment->status === 'paid')
                                        <span class="badge bg-success rounded-pill px-3 py-2"><i class="bi bi-check-circle me-1"></i>ناجحة</span>
                                    @elseif($payment->status === 'pending')
                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2"><i class="bi bi-hourglass-split me-1"></i>قيد المراجعة</span>
                                    @elseif($payment->status === 'failed')
                                        <span class="badge bg-danger rounded-pill px-3 py-2"><i class="bi bi-x-circle me-1"></i>فشلت</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($payment->status === 'paid')
                                        <a href="{{ route('student.receipt', $payment->id) }}" class="btn btn-sm btn-light border rounded-circle" target="_blank" title="طباعة الإيصال">
                                            <i class="bi bi-printer text-primary"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
    .btn-category {
        background: #fff;
        border: 1.5px solid #e8eef6;
        border-radius: 50px;
        padding: 10px 24px;
        font-weight: 700;
        color: #1a2d5a;
        transition: all 0.2s;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .btn-category:hover {
        background: #f8fafc;
        border-color: var(--primary);
    }
    .btn-category.active {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
        box-shadow: 0 4px 15px rgba(26,45,90,0.1);
    }
    .service-section {
        transition: opacity 0.3s ease;
    }
    .service-section.hidden {
        display: none;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
                    if (category === 'all' || section.getAttribute('data-type') === category) {
                        section.classList.remove('hidden');
                    } else {
                        section.classList.add('hidden');
                    }
                });
            });
        });
    });
</script>
<!-- Chatbot Widget -->
<div id="chatbot-widget" class="chatbot-widget">
    <div class="chatbot-header">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-robot fs-4"></i>
            <div>
                <div class="fw-bold">المساعد الذكي للطلاب</div>
                <div style="font-size: 0.7rem; opacity: 0.8;">جاهز للرد على استفساراتك</div>
            </div>
        </div>
        <button id="close-chat" class="btn btn-sm text-white p-0"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="chatbot-messages" id="chatbot-messages">
        <div class="message bot-message">
            أهلاً بيك يا {{ auth()->guard('student')->user()->name }}! أنا المساعد الذكي الخاص بيك. أقدر أساعدك إزاي النهاردة؟
        </div>
    </div>
    <div class="chatbot-input">
        <input type="text" id="chat-input" placeholder="اكتب سؤالك هنا... (مثال: عليا كام؟)">
        <button id="send-chat" class="btn rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:40px;height:40px; background:var(--primary); color:#fff; border:none;">
            <i class="bi bi-send-fill" style="transform: rotate(45deg); margin-left:-2px;"></i>
        </button>
    </div>
</div>

<button id="chatbot-toggle" class="chatbot-toggle shadow-lg">
    <i class="bi bi-chat-dots-fill"></i>
</button>

<style>
    .chatbot-toggle {
        position: fixed; bottom: 30px; left: 30px; width: 60px; height: 60px;
        border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent));
        color: #fff; border: none; font-size: 1.8rem; display: flex; align-items: center; justify-content: center;
        cursor: pointer; z-index: 1000; transition: transform 0.3s;
    }
    .chatbot-toggle:hover { transform: scale(1.1); }
    
    .chatbot-widget {
        position: fixed; bottom: 100px; left: 30px; width: 350px; max-width: calc(100vw - 60px);
        background: #fff; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        display: none; flex-direction: column; overflow: hidden; z-index: 1000;
        border: 1px solid #e5eaf2;
    }
    .chatbot-widget.active { display: flex; animation: slideUp 0.3s ease; }
    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    .chatbot-header {
        background: var(--primary); color: #fff; padding: 15px 20px;
        display: flex; justify-content: space-between; align-items: center;
    }
    
    .chatbot-messages {
        height: 350px; padding: 20px; overflow-y: auto; background: #f8fafc;
        display: flex; flex-direction: column; gap: 10px; scroll-behavior: smooth;
    }
    
    .message { max-width: 85%; padding: 12px 16px; border-radius: 15px; font-size: 0.9rem; line-height: 1.5; position: relative; }
    .bot-message { background: #fff; border: 1px solid #e5eaf2; color: #333; border-bottom-right-radius: 4px; align-self: flex-start; }
    .user-message { background: var(--primary); color: #fff; border-bottom-left-radius: 4px; align-self: flex-end; }
    
    .chatbot-input {
        padding: 15px; background: #fff; border-top: 1px solid #e5eaf2;
        display: flex; gap: 10px; align-items: center;
    }
    .chatbot-input input {
        flex: 1; border: 1px solid #e5eaf2; border-radius: 50px; padding: 10px 15px; outline: none; font-family: 'Tajawal', sans-serif;
    }
    .chatbot-input input:focus { border-color: var(--primary); }
    
    .typing-indicator { display: flex; gap: 4px; padding: 8px 12px; align-items: center; }
    .typing-indicator span { width: 6px; height: 6px; background: #ccc; border-radius: 50%; animation: blink 1.4s infinite both; }
    .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
    .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes blink { 0%, 80%, 100% { opacity: 0.2; transform: scale(0.8); } 40% { opacity: 1; transform: scale(1.2); } }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('chatbot-toggle');
        const widget = document.getElementById('chatbot-widget');
        const closeBtn = document.getElementById('close-chat');
        const input = document.getElementById('chat-input');
        const sendBtn = document.getElementById('send-chat');
        const messagesBox = document.getElementById('chatbot-messages');

        toggleBtn.addEventListener('click', () => {
            widget.classList.toggle('active');
            if (widget.classList.contains('active')) input.focus();
        });
        closeBtn.addEventListener('click', () => widget.classList.remove('active'));

        function appendMessage(text, sender) {
            const div = document.createElement('div');
            div.className = 'message ' + (sender === 'user' ? 'user-message' : 'bot-message');
            // Allow basic markdown bold **text** -> <strong>text</strong>
            text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            div.innerHTML = text;
            messagesBox.appendChild(div);
            messagesBox.scrollTop = messagesBox.scrollHeight;
        }

        function showTyping() {
            const div = document.createElement('div');
            div.className = 'message bot-message typing-box';
            div.innerHTML = '<div class="typing-indicator"><span></span><span></span><span></span></div>';
            messagesBox.appendChild(div);
            messagesBox.scrollTop = messagesBox.scrollHeight;
        }

        function removeTyping() {
            const typing = messagesBox.querySelector('.typing-box');
            if (typing) typing.remove();
        }

        async function sendMessage() {
            const msg = input.value.trim();
            if (!msg) return;

            appendMessage(msg, 'user');
            input.value = '';
            showTyping();

            try {
                const response = await fetch("{{ route('student.chat') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message: msg })
                });
                const data = await response.json();
                removeTyping();
                if (response.ok) {
                    appendMessage(data.reply, 'bot');
                } else {
                    appendMessage(data.reply || 'عذراً، حدث خطأ.', 'bot');
                }
            } catch (error) {
                removeTyping();
                appendMessage('معلش، مش قادر أرد دلوقتي عشان في مشكلة في الشبكة.', 'bot');
            }
        }

        sendBtn.addEventListener('click', sendMessage);
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });
    });
</script>
</body>
</html>
