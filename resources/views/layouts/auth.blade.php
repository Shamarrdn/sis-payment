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
            --primary: #1a2d5a;
            --accent: #c8a96e;
        }
        * { box-sizing: border-box; }
        body { font-family: 'Tajawal', sans-serif; background: #f0f4f8; min-height: 100vh; }

        /* ─── Hero Banner ─── */
        .hero-banner {
            position: relative;
            height: 260px;
            overflow: hidden;
        }

        .hero-banner img {
            width: 100%; height: 100%;
            object-fit: cover;
            object-position: center 30%;
        }

        .hero-banner .overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to bottom, rgba(26,45,90,0.55) 0%, rgba(26,45,90,0.9) 100%);
            display: flex; flex-direction: column;
            align-items: center; justify-content: flex-end;
            padding-bottom: 30px;
            text-align: center;
        }

        .hero-banner .overlay h1 {
            color: #fff; font-weight: 800;
            font-size: 1.8rem; margin-bottom: 4px;
        }

        .hero-banner .overlay p {
            color: var(--accent); font-size: 1rem;
        }

        /* ─── Auth Card ─── */
        .auth-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.12);
            overflow: hidden;
            border: 1px solid #e8eef6;
        }

        .auth-card .card-header-uni {
            background: var(--primary);
            color: #fff;
            padding: 20px 30px;
            font-size: 1.1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .auth-card .card-body {
            padding: 30px;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 1.5px solid #dde4f0;
            padding: 11px 14px;
            font-family: 'Tajawal', sans-serif;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26,45,90,0.1);
        }

        .form-label { font-weight: 600; font-size: 0.9rem; color: #444; }

        .btn-signin {
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 13px;
            font-family: 'Tajawal', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            width: 100%;
            transition: all 0.2s;
            cursor: pointer;
        }

        .btn-signin:hover {
            background: #233872;
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(26,45,90,0.3);
        }

        footer { padding: 20px; text-align: center; color: #aab; font-size: 0.85rem; }
    </style>
</head>
<body>

{{-- Hero --}}
<div class="hero-banner">
    <img src="{{ asset('images/uni.jpg') }}" alt="University Building">
    <div class="overlay">
        <h1>جامعة بني سويف التكنولوجية</h1>
        <p>@yield('hero-subtitle', 'بوابة الدفع الإلكتروني')</p>
    </div>
</div>

{{-- Content --}}
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-xl-5 col-lg-7">
            @yield('content')
        </div>
    </div>
</div>

<footer>
    &copy; {{ date('Y') }} جميع الحقوق محفوظة لجامعة بني سويف التكنولوجية.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
