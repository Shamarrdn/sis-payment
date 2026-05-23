<span class="nav-label">الخدمات</span>
<a href="{{ route('student.dashboard') }}" class="{{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
    <i class="bi bi-grid-fill"></i> الخدمات المتاحة
</a>
<a href="{{ route('student.history') }}" class="{{ request()->routeIs('student.history') ? 'active' : '' }}">
    <i class="bi bi-archive-fill"></i> الأرشيف الرقمي
</a>
<a href="{{ route('student.requests') }}" class="{{ request()->routeIs('student.requests') ? 'active' : '' }}">
    <i class="bi bi-list-check"></i> متابعة الطلبات
</a>
<a href="{{ route('student.profile') }}" class="{{ request()->routeIs('student.profile') ? 'active' : '' }}">
    <i class="bi bi-pencil-square"></i> تكملة بياناتي
</a>

<span class="nav-label">التواصل والدعم</span>
<a href="{{ route('student.announcements') }}" class="{{ request()->routeIs('student.announcements') ? 'active' : '' }}">
    <i class="bi bi-megaphone-fill"></i> الإعلانات
</a>
<a href="{{ route('student.notifications') }}" class="{{ request()->routeIs('student.notifications*') ? 'active' : '' }}">
    <i class="bi bi-bell-fill"></i> الإشعارات
    @php $navUnread = auth()->guard('student')->user()->unreadNotifications()->count(); @endphp
    @if($navUnread > 0)<span class="badge bg-danger ms-auto">{{ $navUnread }}</span>@endif
</a>
<a href="{{ route('student.faq') }}" class="{{ request()->routeIs('student.faq') ? 'active' : '' }}">
    <i class="bi bi-question-circle-fill"></i> الأسئلة الشائعة
</a>
<a href="{{ route('student.help') }}" class="{{ request()->routeIs('student.help') ? 'active' : '' }}">
    <i class="bi bi-book-fill"></i> مركز المساعدة
</a>
<a href="{{ route('student.tickets.index') }}" class="{{ request()->routeIs('student.tickets.*') ? 'active' : '' }}">
    <i class="bi bi-headset"></i> تذاكر الدعم
</a>
