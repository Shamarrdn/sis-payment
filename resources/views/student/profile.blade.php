<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تكملة بيانات الطالب</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1a2d5a; --accent: #c8a96e; --sidebar-w: 260px; }
        body { font-family: 'Tajawal', sans-serif; background: #f0f4f8; }
        .sidebar { width: var(--sidebar-w); min-height: 100vh; background: linear-gradient(180deg, var(--primary) 0%, #233872 100%); position: fixed; top: 0; right: 0; display: flex; flex-direction: column; box-shadow: -4px 0 20px rgba(0,0,0,0.15); z-index: 100; }
        .sidebar-logo { padding: 20px 16px; background: rgba(0,0,0,0.2); text-align: center; }
        .sidebar-logo img { width: 60px; height: 60px; border-radius: 50%; border: 3px solid var(--accent); object-fit: cover; }
        .sidebar-logo h6 { color: #fff; font-weight: 700; margin-top: 8px; font-size: 0.85rem; }
        .sidebar-logo small { color: var(--accent); font-size: 0.78rem; }
        .sidebar-nav { flex: 1; padding: 16px 12px; }
        .sidebar-nav .nav-label { font-size: 0.7rem; font-weight: 700; color: rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 0.1em; padding: 12px 12px 6px; display: block; }
        .sidebar-nav a { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 10px; color: rgba(255,255,255,0.8); text-decoration: none; font-size: 0.92rem; font-weight: 500; margin-bottom: 4px; transition: all 0.2s; }
        .sidebar-nav a.active { background: rgba(200,169,110,0.25); color: var(--accent); border-right: 3px solid var(--accent); }
        .sidebar-footer { padding: 14px 12px; border-top: 1px solid rgba(255,255,255,0.1); }
        .sidebar-footer form button { width: 100%; background: rgba(220,53,69,0.15); color: #ff6b7a; border: 1px solid rgba(220,53,69,0.3); border-radius: 10px; padding: 10px; font-family: 'Tajawal',sans-serif; font-size: 0.88rem; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; transition: all 0.2s; }
        .main-content { margin-right: var(--sidebar-w); }
        .top-header { background: #fff; border-bottom: 1px solid #e5eaf2; padding: 14px 30px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50; box-shadow: 0 2px 12px rgba(0,0,0,0.04); }
        .top-header h5 { font-weight: 700; color: var(--primary); margin: 0; }
        .user-badge { display: flex; align-items: center; gap: 10px; background: #f0f4f8; padding: 6px 14px 6px 6px; border-radius: 50px; font-size: 0.88rem; font-weight: 600; color: var(--primary); }
        .user-badge .avatar { width: 34px; height: 34px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; }
        .user-badge.dropdown-toggle::after { display: none; }
        .dropdown-item:active { background-color: var(--primary); }
        .dropdown-item i { transition: transform 0.2s; }
        .dropdown-item:hover i { transform: scale(1.1); }
        .content-area { padding: 30px; }
        .form-box { background: #fff; border-radius: 24px; padding: 30px; box-shadow: 0 12px 30px rgba(15,23,42,0.08); border: 1px solid #e8edf6; }
        .form-box h3 { margin-bottom: 18px; color: #1a2d5a; font-weight: 800; }
        .form-box .form-label { font-weight: 700; color: #344054; }
        .form-box .form-control, .form-box .form-select { border-radius: 12px; }
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
        <span class="nav-label">الخدمات</span>
        <a href="{{ route('student.dashboard') }}" class="{{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-fill"></i> الخدمات المتاحة
        </a>
        <a href="{{ route('student.history') }}" class="{{ request()->routeIs('student.history') ? 'active' : '' }}">
            <i class="bi bi-archive-fill"></i> الأرشيف الرقمي
        </a>
        <a href="{{ route('student.profile') }}" class="{{ request()->routeIs('student.profile') ? 'active' : '' }}">
            <i class="bi bi-pencil-square"></i> تكملة بياناتي
        </a>
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
        @if($errors->any())
            <div class="alert alert-danger rounded-3 mb-4">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="form-box">
            <h3>تعديل وتكملة البيانات الشخصية</h3>
            <p class="text-muted">يمكنك تحديث بياناتك الشخصية وبيانات الكلية والقسم هنا.</p>

            <form action="{{ route('student.profile.update') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">الاسم الكامل</label>
                        <input type="text" name="name" value="{{ old('name', $student->name) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">الرقم القومي</label>
                        <input type="text" value="{{ $student->national_id }}" class="form-control" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="text" name="phone" value="{{ old('phone', $student->phone) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">الفرقة / السنة</label>
                        <input type="text" name="academic_year" value="{{ old('academic_year', $student->academic_year) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">التخصص</label>
                        <input type="text" name="program" value="{{ old('program', $student->program) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">الفئة الخاصة</label>
                        <input type="text" name="special_category" value="{{ old('special_category', $student->special_category) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">فئة المستخدم</label>
                        <input type="text" name="user_category" value="{{ old('user_category', $student->user_category) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">الكلية</label>
                        <select id="facultySelect" name="faculty_id" class="form-select">
                            <option value="">اختر الكلية</option>
                            @foreach($faculties as $faculty)
                                <option value="{{ $faculty->id }}" {{ old('faculty_id', $student->faculty_id) == $faculty->id ? 'selected' : '' }}>{{ $faculty->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">القسم</label>
                        <select id="departmentSelect" name="department_id" class="form-select">
                            <option value="">اختر القسم</option>
                            @if($student->faculty_id)
                                @foreach($faculties->firstWhere('id', $student->faculty_id)?->activeDepartments ?? [] as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', $student->department_id) == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary px-4 py-2" style="background:var(--primary);border-radius:12px;">
                        <i class="bi bi-save me-2"></i> حفظ البيانات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const facultyDepartments = @json($faculties->mapWithKeys(function ($faculty) {
        return [$faculty->id => $faculty->activeDepartments->map(function ($dept) {
            return ['id' => $dept->id, 'name' => $dept->name];
        })->toArray()];
    }));

    const facultySelect = document.getElementById('facultySelect');
    const departmentSelect = document.getElementById('departmentSelect');

    facultySelect?.addEventListener('change', function () {
        const selected = this.value;
        departmentSelect.innerHTML = '<option value="">اختر القسم</option>';

        if (!selected || !facultyDepartments[selected]) {
            return;
        }

        facultyDepartments[selected].forEach(dept => {
            const option = document.createElement('option');
            option.value = dept.id;
            option.textContent = dept.name;
            departmentSelect.appendChild(option);
        });
    });
</script>
</body>
</html>
