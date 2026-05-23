<span class="nav-label">التقارير</span>
<a href="{{ route('reports.today') }}" class="{{ request()->routeIs('reports.today') ? 'active' : '' }}">
    <i class="bi bi-calendar-day"></i> ملخص اليوم
</a>
<a href="{{ route('reports.monthly') }}" class="{{ request()->routeIs('reports.monthly') ? 'active' : '' }}">
    <i class="bi bi-calendar-month"></i> ملخص شهري
</a>
<a href="{{ route('reports.delayed') }}" class="{{ request()->routeIs('reports.delayed') ? 'active' : '' }}">
    <i class="bi bi-exclamation-triangle"></i> طلبات متأخرة
</a>
<a href="{{ route('reports.popular') }}" class="{{ request()->routeIs('reports.popular') ? 'active' : '' }}">
    <i class="bi bi-graph-up"></i> أكثر الخدمات
</a>
<a href="{{ route('reports.recent') }}" class="{{ request()->routeIs('reports.recent') ? 'active' : '' }}">
    <i class="bi bi-activity"></i> آخر العمليات
</a>
