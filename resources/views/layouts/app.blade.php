<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'جامعة بني سويف التكنولوجية')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #0f172a;
            --primary-light: #1e293b;
            --accent: #d4af37; /* Metallic Gold */
            --accent-soft: rgba(212, 175, 55, 0.15);
            --bg: #f8fafc;
            --sidebar-width: 280px;
            --glass: rgba(255, 255, 255, 0.7);
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.02);
            --shadow-md: 0 10px 30px rgba(0,0,0,0.06);
            --shadow-lg: 0 20px 50px rgba(15, 23, 42, 0.08);
            --radius: 18px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Tajawal', sans-serif;
            background: var(--bg);
            color: #1e293b;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ─── Sidebar Layout ─── */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            position: fixed;
            top: 0; right: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
            box-shadow: -10px 0 30px rgba(0,0,0,0.1);
            overflow: hidden; /* Contain children */
        }

        .sidebar-logo {
            padding: 40px 20px;
            text-align: center;
        }

        .sidebar-logo img {
            width: 85px; height: 85px;
            border-radius: 50%;
            border: 4px solid var(--accent);
            padding: 4px;
            object-fit: cover;
            filter: drop-shadow(0 0 10px rgba(212, 175, 55, 0.3));
        }

        .sidebar-logo h6 {
            color: #fff;
            font-weight: 800;
            font-size: 1.1rem;
            margin-top: 15px;
            letter-spacing: 0.5px;
        }

        .sidebar-logo small {
            color: var(--accent);
            font-size: 0.8rem;
            text-transform: uppercase;
            font-weight: 700;
            opacity: 0.9;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.2) transparent;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.15);
            border-radius: 10px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.3);
        }

        .sidebar-nav .nav-label {
            font-size: 0.72rem;
            font-weight: 700;
            color: rgba(255,255,255,0.4);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 12px 12px 6px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            border-radius: 10px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            margin-bottom: 4px;
            transition: all 0.2s;
        }

        .sidebar-nav a i { font-size: 1.1rem; }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: rgba(255,255,255,0.12);
            color: #fff;
            transform: translateX(-2px);
        }

        .sidebar-nav a.active {
            background: rgba(200, 169, 110, 0.25);
            color: var(--accent);
            border-right: 3px solid var(--accent);
        }

        .sidebar-footer {
            padding: 14px 12px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-footer form button {
            width: 100%;
            background: rgba(220,53,69,0.15);
            color: #ff6b7a;
            border: 1px solid rgba(220,53,69,0.3);
            border-radius: 10px;
            padding: 10px;
            font-family: 'Tajawal', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .sidebar-footer form button:hover {
            background: rgba(220,53,69,0.3);
            color: #fff;
        }

        /* ─── Main Content ─── */
        .main-content {
            margin-right: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ─── Top Header ─── */
        .top-header {
            background: #fff;
            border-bottom: 1px solid #e5eaf2;
            padding: 14px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        }

        .top-header h5 {
            font-weight: 700;
            color: var(--primary);
            margin: 0;
        }

        .top-header .user-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--bg);
            padding: 6px 14px 6px 6px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--primary);
        }

        .top-header .user-badge .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        /* ─── Content Area ─── */
        .content-area {
            flex: 1;
            padding: 30px;
        }

        /* ─── Cards ─── */
        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid #e8eef6;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }

        .stat-card .icon-wrap {
            width: 56px; height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 16px;
        }

        /* ─── Tables ─── */
        .themed-table {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid #e8eef6;
        }

        .themed-table table {
            margin: 0;
        }

        .themed-table thead th {
            background: var(--primary);
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 14px 18px;
            border: none;
        }

        .themed-table tbody td {
            padding: 13px 18px;
            border-color: #f0f4f8;
            font-size: 0.92rem;
            vertical-align: middle;
        }

        .themed-table tbody tr:hover {
            background: #f7f9fc;
        }

        /* ─── Buttons ─── */
        .btn-primary-uni {
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 24px;
            font-family: 'Tajawal', sans-serif;
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary-uni:hover {
            background: var(--primary-light);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(26,45,90,0.3);
        }

        /* ─── Forms ─── */
        .form-control, .form-select {
            border-radius: 10px;
            border: 1.5px solid #dde4f0;
            padding: 10px 14px;
            font-family: 'Tajawal', sans-serif;
            font-size: 0.95rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26,45,90,0.1);
        }

        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #444;
            margin-bottom: 6px;
        }

        /* ─── Alerts ─── */
        .alert {
            border-radius: 12px;
            border: none;
            font-size: 0.94rem;
        }

        /* ─── Page Title ─── */
        .page-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 6px;
        }

        .page-subtitle {
            color: #7a8aaa;
            font-size: 0.95rem;
            margin-bottom: 28px;
        }

        @yield('extra-styles')
    </style>
</head>
<body>

{{-- ─── Sidebar ─── --}}
<div class="sidebar">
    <div class="sidebar-logo">
        <img src="{{ asset('images/uni.jpg') }}" alt="Logo">
        <h6>نظام SIS الموحد</h6>
        <small>جامعة بني سويف التكنولوجية</small>
    </div>

    <nav class="sidebar-nav">
        @include('partials.sidebar')
    </nav>

    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit">
                <i class="bi bi-box-arrow-right"></i> تسجيل الخروج
            </button>
        </form>
    </div>
</div>

{{-- ─── Main ─── --}}
<div class="main-content">
    <div class="top-header">
        <h5>@yield('page-heading', 'لوحة التحكم')</h5>
        <div class="user-badge">
            <div class="avatar"><i class="bi bi-person-fill"></i></div>
            @yield('user-name', 'مرحباً')
        </div>
    </div>

    <div class="content-area">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <ul class="mb-0 list-unstyled">
                    @foreach($errors->all() as $error)
                        <li><i class="bi bi-exclamation-circle-fill me-2"></i>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
