<div class="sidebar">
    <div class="sidebar-logo">
        <img src="{{ asset('images/uni.jpg') }}" alt="">
        <h6>{{ auth()->guard('student')->user()->name }}</h6>
        <small style="color:var(--accent);font-size:0.75rem;">{{ auth()->guard('student')->user()->facultyName() }}</small>
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
