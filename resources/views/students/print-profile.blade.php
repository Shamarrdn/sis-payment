<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ملف الطالب - {{ $student->name }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>body{font-family:Tajawal,sans-serif;padding:40px;} @media print{.no-print{display:none}}</style>
</head>
<body>
<button class="btn btn-primary no-print mb-4" onclick="window.print()">طباعة / حفظ PDF</button>
<h2 class="fw-bold mb-4">ملف الطالب — {{ $student->name }}</h2>
<table class="table table-bordered">
    <tr><th>الكود المرجعي</th><td>{{ $student->reference_number }}</td></tr>
    <tr><th>الرقم القومي</th><td>{{ $student->national_id }}</td></tr>
    <tr><th>الكلية</th><td>{{ $student->facultyName() }}</td></tr>
    <tr><th>القسم</th><td>{{ $student->departmentName() }}</td></tr>
    <tr><th>الفرقة</th><td>{{ $student->academic_year }}</td></tr>
    <tr><th>الحالة</th><td>{{ $student->status }}</td></tr>
    <tr><th>الهاتف</th><td>{{ $student->phone }}</td></tr>
    <tr><th>البريد</th><td>{{ $student->email }}</td></tr>
    <tr><th>العنوان</th><td>{{ $student->address }}</td></tr>
</table>
<p class="text-muted small mt-4">تاريخ الطباعة: {{ now()->format('Y-m-d H:i') }}</p>
</body>
</html>
