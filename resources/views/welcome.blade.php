<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بوابة جامعة بني سويف التكنولوجية</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1a2d5a; --accent: #c8a96e; }
        body { font-family: 'Tajawal', sans-serif; background: #f0f4f8; }

        .hero {
            position: relative; height: 420px; overflow: hidden;
        }
        .hero img { width: 100%; height: 100%; object-fit: cover; object-position: center 30%; }
        .hero .overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to bottom, rgba(26,45,90,0.5) 0%, rgba(26,45,90,0.88) 100%);
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            text-align: center; padding: 20px;
        }
        .hero .overlay img.logo {
            width: 90px; height: 90px;
            border-radius: 50%; object-fit: cover;
            border: 4px solid var(--accent);
            margin-bottom: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .hero .overlay h1 { color: #fff; font-weight: 800; font-size: 2.2rem; margin-bottom: 8px; }
        .hero .overlay p { color: var(--accent); font-size: 1.1rem; font-weight: 500; }

        .portal-card {
            background: #fff;
            border-radius: 20px;
            border: 1px solid #e0e8f4;
            box-shadow: 0 4px 20px rgba(0,0,0,0.07);
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
        }
        .portal-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(26,45,90,0.15);
        }
        .portal-card .card-header-colored {
            padding: 28px;
            text-align: center;
        }
        .portal-card .icon-hero {
            width: 80px; height: 80px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 14px;
            font-size: 2.2rem;
        }
        .portal-card h4 { font-weight: 800; font-size: 1.3rem; margin-bottom: 8px; }
        .portal-card p { color: #7a8aaa; font-size: 0.95rem; line-height: 1.6; min-height: 60px; }

        .btn-portal {
            display: block;
            text-align: center;
            padding: 12px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            font-family: 'Tajawal', sans-serif;
            text-decoration: none;
            transition: all 0.2s;
            margin: 16px 20px 20px;
        }

        footer { padding: 24px; text-align: center; color: #aab; font-size: 0.85rem; border-top: 1px solid #e5eaf2; }
    </style>
</head>
<body>

<div class="hero">
    <img src="{{ asset('images/uni.jpeg') }}" alt="الجامعة">
    <div class="overlay">
        <img src="{{ asset('images/uni.jpg') }}" class="logo" alt="Logo">
        <h1>جامعة بني سويف التكنولوجية</h1>
        <p>نظام الدفع الإلكتروني الموحد للخدمات الجامعية</p>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center g-4">

        {{-- Student Portal --}}
        <div class="col-lg-5">
            <div class="portal-card">
                <div class="card-header-colored">
                    <div class="icon-hero" style="background:#eff3fb;color:#1a2d5a;">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <h4 style="color:#1a2d5a;">بوابة الطلاب</h4>
                    <p>سداد الرسوم الدراسية والخدمات الجامعية المختلفة عبر بوابة الدفع الإلكتروني.</p>
                </div>
                <a href="{{ route('student.login') }}" class="btn-portal" style="background:#1a2d5a;color:#fff;">
                    <i class="bi bi-box-arrow-in-right me-2"></i> دخول الطلاب
                </a>
            </div>
        </div>

        {{-- Employee Portal --}}
        <div class="col-lg-5">
            <div class="portal-card">
                <div class="card-header-colored">
                    <div class="icon-hero" style="background:#fff8ee;color:#c8a96e;">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <h4 style="color:#1a2d5a;">بوابة الموظفين</h4>
                    <p>تسجيل دخول لموظفي شئون الطلاب والشئون المالية لإدارة السجلات والتسويات.</p>
                </div>
                <a href="{{ route('login') }}" class="btn-portal" style="background:#c8a96e;color:#fff;">
                    <i class="bi bi-box-arrow-in-right me-2"></i> دخول الموظفين
                </a>
            </div>
        </div>

    </div>
</div>

<footer>
    &copy; {{ date('Y') }} جميع الحقوق محفوظة لجامعة بني سويف التكنولوجية.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
