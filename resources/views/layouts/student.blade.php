<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'بوابة الطالب')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1a2d5a; --accent: #c8a96e; --sidebar-w: 260px; }
        body { font-family: 'Tajawal', sans-serif; background: #f0f4f8; }
        .sidebar {
            width: var(--sidebar-w); min-height: 100vh;
            background: linear-gradient(180deg, var(--primary) 0%, #233872 100%);
            position: fixed; top: 0; right: 0; display: flex; flex-direction: column;
            box-shadow: -4px 0 20px rgba(0,0,0,0.15); z-index: 100;
        }
        .sidebar-logo { padding: 20px 16px; background: rgba(0,0,0,0.2); text-align: center; }
        .sidebar-logo img { width: 60px; height: 60px; border-radius: 50%; border: 3px solid var(--accent); object-fit: cover; }
        .sidebar-logo h6 { color: #fff; font-weight: 700; margin-top: 8px; font-size: 0.85rem; }
        .sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }
        .sidebar-nav .nav-label { font-size: 0.7rem; font-weight: 700; color: rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 0.1em; padding: 12px 12px 6px; display: block; }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 10px;
            color: rgba(255,255,255,0.8); text-decoration: none; font-size: 0.88rem; font-weight: 500; margin-bottom: 4px;
        }
        .sidebar-nav a.active { background: rgba(200,169,110,0.25); color: var(--accent); border-right: 3px solid var(--accent); }
        .sidebar-footer { padding: 14px 12px; border-top: 1px solid rgba(255,255,255,0.1); }
        .sidebar-footer form button {
            width: 100%; background: rgba(220,53,69,0.15); color: #ff6b7a; border: 1px solid rgba(220,53,69,0.3);
            border-radius: 10px; padding: 10px; font-family: 'Tajawal',sans-serif; font-weight: 600; cursor: pointer;
        }
        .main-content { margin-right: var(--sidebar-w); }
        .top-header {
            background: #fff; border-bottom: 1px solid #e5eaf2; padding: 14px 30px;
            display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50;
        }
        .top-header h5 { font-weight: 700; color: var(--primary); margin: 0; }
        .content-area { padding: 30px; }
        .notif-badge { position: absolute; top: -4px; left: -4px; background: #dc3545; color: #fff; font-size: 0.65rem; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; }
    </style>
    @stack('styles')
</head>
<body>
@include('student.partials.sidebar')

<div class="main-content">
    <div class="top-header">
        <h5>@yield('page-title', 'بوابة الطالب الإلكترونية')</h5>
        <div class="d-flex align-items-center gap-3">
            @php $unread = auth()->guard('student')->user()->unreadNotifications()->count(); @endphp
            <a href="{{ route('student.notifications') }}" class="btn btn-light position-relative rounded-circle" style="width:42px;height:42px;">
                <i class="bi bi-bell-fill text-primary"></i>
                @if($unread > 0)
                    <span class="notif-badge">{{ $unread > 9 ? '9+' : $unread }}</span>
                @endif
            </a>
        </div>
    </div>
    <div class="content-area">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif
        @yield('content')
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
