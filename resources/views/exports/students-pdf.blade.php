<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head><meta charset="UTF-8"><title>قائمة الطلاب</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
<style>body{font-family:Tajawal,sans-serif;padding:24px;} @media print{.no-print{display:none}}</style></head>
<body>
<button class="btn btn-primary no-print mb-3" onclick="window.print()">طباعة / PDF</button>
<h3>قائمة الطلاب — {{ now()->format('Y-m-d') }}</h3>
<table class="table table-bordered table-sm mt-3"><thead><tr><th>#</th><th>الاسم</th><th>الكود</th><th>الكلية</th><th>القسم</th><th>الفرقة</th></tr></thead>
<tbody>@foreach($students as $i => $s)<tr><td>{{ $i+1 }}</td><td>{{ $s->name }}</td><td>{{ $s->reference_number }}</td><td>{{ $s->facultyName() }}</td><td>{{ $s->departmentName() }}</td><td>{{ $s->academic_year }}</td></tr>@endforeach</tbody></table>
</body></html>
