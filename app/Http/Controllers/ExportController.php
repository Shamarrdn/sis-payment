<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Student;
use App\Services\AuditLoggerService;
use App\Services\ScopeService;

class ExportController extends Controller
{
    /**
     * Export payments as CSV with full filters.
     * Requires 'export_data' permission.
     */
    public function payments(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermission('export_data')) {
            abort(403, 'ليس لديك صلاحية تصدير البيانات.');
        }

        $query = Payment::with(['student', 'service'])->orderByDesc('payment_date');

        // Apply the same filters as the dashboard
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('reference_number', 'like', "%$s%")
                  ->orWhereHas('student', fn($sq) =>
                      $sq->where('name', 'like', "%$s%")->orWhere('national_id', 'like', "%$s%")
                  );
            });
        }
        if ($request->filled('status'))        $query->where('status', $request->status);
        if ($request->filled('program'))       $query->where('program', 'like', "%{$request->program}%");
        if ($request->filled('user_category')) $query->where('user_category', $request->user_category);
        if ($request->filled('date_from'))     $query->whereDate('payment_date', '>=', $request->date_from);
        if ($request->filled('date_to'))       $query->whereDate('payment_date', '<=', $request->date_to);

        $payments = $query->get();

        AuditLoggerService::log('Export Payments CSV', null, null, [
            'filters' => $request->only('status', 'program', 'user_category', 'date_from', 'date_to'),
            'count'   => $payments->count(),
        ]);

        $filename = 'payments_export_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
        ];

        $callback = function () use ($payments) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fputs($handle, "\xEF\xBB\xBF");

            // Headers
            fputcsv($handle, [
                'رقم المرجع',
                'اسم الطالب',
                'الرقم القومي',
                'البرنامج',
                'فئة المستخدم',
                'العام الدراسي',
                'الخدمة',
                'الكمية',
                'سعر الوحدة',
                'الإجمالي',
                'وسيلة الدفع',
                'الحالة',
                'حالة الاسترداد',
                'تاريخ الدفع',
                'ملاحظات',
            ]);

            foreach ($payments as $p) {
                fputcsv($handle, [
                    $p->reference_number,
                    optional($p->student)->name,
                    optional($p->student)->national_id,
                    $p->program,
                    $p->user_category,
                    $p->academic_year,
                    optional($p->service)->name,
                    $p->quantity,
                    $p->amount,
                    $p->total_amount,
                    $p->payment_method,
                    $p->status,
                    $p->refund_status,
                    optional($p->payment_date)->format('Y-m-d H:i'),
                    $p->notes,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export daily settlement report.
     */
    public function dailySettlement(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermission('export_data')) {
            abort(403, 'ليس لديك صلاحية تصدير البيانات.');
        }

        $date = $request->filled('date') ? $request->date : today()->toDateString();

        $payments = Payment::with(['student', 'service'])
            ->whereDate('payment_date', $date)
            ->where('status', 'paid')
            ->get();

        $totalRevenue = $payments->sum('total_amount');
        $filename = 'daily_settlement_' . $date . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($payments, $date, $totalRevenue) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['التسوية اليومية - تاريخ: ' . $date]);
            fputcsv($handle, ['إجمالي الإيرادات: ' . number_format($totalRevenue) . ' ج.م']);
            fputcsv($handle, ['عدد العمليات: ' . $payments->count()]);
            fputcsv($handle, []);
            fputcsv($handle, ['رقم المرجع', 'الطالب', 'الخدمة', 'المبلغ', 'وسيلة الدفع', 'الوقت']);

            foreach ($payments as $p) {
                fputcsv($handle, [
                    $p->reference_number,
                    optional($p->student)->name,
                    optional($p->service)->name,
                    $p->total_amount,
                    $p->payment_method,
                    optional($p->payment_date)->format('H:i'),
                ]);
            }
            fclose($handle);
        };

        AuditLoggerService::log('Export Daily Settlement', null, null, ['date' => $date, 'count' => $payments->count()]);
        return response()->stream($callback, 200, $headers);
    }

    public function students(Request $request)
    {
        if (!auth()->user()->hasPermission('export_data') && !in_array(auth()->user()->role, ['student_affairs', 'super_admin', 'admin'])) {
            abort(403, 'ليس لديك صلاحية تصدير البيانات.');
        }

        $query = Student::with(['faculty', 'department']);
        $query = ScopeService::scopeStudents($query, auth()->user());

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('national_id', 'like', "%{$s}%")
                    ->orWhere('reference_number', 'like', "%{$s}%");
            });
        }
        if ($request->filled('faculty_id')) {
            $query->where('faculty_id', $request->faculty_id);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $students = $query->orderBy('name')->get();
        $format = $request->input('format', 'csv');

        if ($format === 'pdf') {
            return view('exports.students-pdf', compact('students'));
        }

        $filename = 'students_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($students) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['الاسم', 'الرقم القومي', 'الكود المرجعي', 'الكلية', 'القسم', 'الفرقة', 'الحالة', 'الهاتف', 'البريد']);
            foreach ($students as $st) {
                fputcsv($handle, [
                    $st->name,
                    $st->national_id,
                    $st->reference_number,
                    $st->facultyName(),
                    $st->departmentName(),
                    $st->academic_year,
                    $st->status,
                    $st->phone,
                    $st->email,
                ]);
            }
            fclose($handle);
        };

        AuditLoggerService::log('Export Students', null, null, ['count' => $students->count()]);

        return response()->stream($callback, 200, $headers);
    }
}
