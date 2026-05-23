@php
    $user = auth()->user();
@endphp

{{-- ─── Main Labels ─── --}}
<span class="nav-label">الأساسية</span>
@if($user->role === 'super_admin' || $user->role === 'admin' || $user->role === 'financial_affairs')
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i> نظرة عامة
    </a>
    <a href="{{ route('admin.statistics.index') }}" class="{{ request()->routeIs('admin.statistics.*') ? 'active' : '' }}">
        <i class="bi bi-bar-chart-line-fill"></i> إحصائيات متقدمة
    </a>
@endif

{{-- ─── Scoped Sections ─── --}}
@if($user->role === 'student_affairs' || $user->role === 'super_admin')
    <span class="nav-label">شئون الطلاب</span>
    <a href="{{ route('affairs.student.index') }}" class="{{ request()->routeIs('affairs.student.*') ? 'active' : '' }}">
        <i class="bi bi-people-fill"></i> إدارة الطلاب
    </a>
    <a href="{{ route('affairs.student.create') }}" class="{{ request()->routeIs('affairs.student.create') ? 'active' : '' }}">
        <i class="bi bi-person-plus-fill"></i> تسجيل طالب جديد
    </a>
    <a href="{{ route('staff.tickets.index') }}" class="{{ request()->routeIs('staff.tickets.*') ? 'active' : '' }}">
        <i class="bi bi-headset"></i> تذاكر الدعم
    </a>
@endif

@if($user->role === 'financial_affairs' || $user->role === 'super_admin')
    <span class="nav-label">الشئون المالية</span>
    <a href="{{ route('affairs.financial.index') }}" class="{{ request()->routeIs('affairs.financial.index') ? 'active' : '' }}">
        <i class="bi bi-wallet2"></i> التسوية اليومية
    </a>
    <a href="{{ route('affairs.financial.payments') }}" class="{{ request()->routeIs('affairs.financial.payments') ? 'active' : '' }}">
        <i class="bi bi-receipt-cutoff"></i> تقارير المدفوعات
    </a>
    <a href="{{ route('admin.services.index') }}" class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
        <i class="bi bi-gear-fill"></i> الخدمات والأسعار
    </a>
@endif

{{-- ─── System Admin Sections ─── --}}
@if($user->role === 'super_admin' || $user->role === 'admin')
    <span class="nav-label">التواصل مع الطلاب</span>
    <a href="{{ route('admin.announcements.index') }}" class="{{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
        <i class="bi bi-megaphone-fill"></i> الإعلانات
    </a>
    <a href="{{ route('admin.faqs.index') }}" class="{{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}">
        <i class="bi bi-question-circle-fill"></i> الأسئلة الشائعة
    </a>
    <a href="{{ route('admin.help.index') }}" class="{{ request()->routeIs('admin.help.*') ? 'active' : '' }}">
        <i class="bi bi-book-fill"></i> مركز المساعدة
    </a>
    <a href="{{ route('staff.tickets.index') }}" class="{{ request()->routeIs('staff.tickets.*') ? 'active' : '' }}">
        <i class="bi bi-headset"></i> تذاكر الدعم
    </a>
@endif

@if($user->role === 'super_admin')
    <span class="nav-label">البنية التنظيمية</span>
    <a href="{{ route('admin.faculties.index') }}" class="{{ request()->routeIs('admin.faculties.*') ? 'active' : '' }}">
        <i class="bi bi-building"></i> الكليات والأقسام
    </a>
    <a href="{{ route('admin.tuition.index') }}" class="{{ request()->routeIs('admin.tuition.*') ? 'active' : '' }}">
        <i class="bi bi-cash-stack"></i> إعدادات الرسوم
    </a>
    <a href="{{ route('admin.employees.index') }}" class="{{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
        <i class="bi bi-person-badge"></i> الموظفين والأذونات
    </a>
@endif

{{-- ─── Monitoring ─── --}}
@if($user->role === 'super_admin' || $user->role === 'admin' || $user->role === 'financial_affairs')
    <span class="nav-label">المراجعة والرقابة</span>
    <a href="{{ route('admin.refunds.index') }}" class="{{ request()->routeIs('admin.refunds.*') ? 'active' : '' }}">
        <i class="bi bi-arrow-counterclockwise"></i> طلبات الاسترداد
    </a>
    <a href="{{ route('admin.review.pending') }}" class="{{ request()->routeIs('admin.review.*') ? 'active' : '' }}">
        <i class="bi bi-hourglass-split"></i> العمليات المعلقة
    </a>
    <a href="{{ route('admin.audit.index') }}" class="{{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
        <i class="bi bi-journal-text"></i> سجل الحركات
    </a>
    <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
        <i class="bi bi-sliders"></i> الإعدادات العامة
    </a>
@endif
