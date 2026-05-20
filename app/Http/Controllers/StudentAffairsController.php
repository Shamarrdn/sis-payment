<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Student;
use App\Services\AuditLoggerService;
use App\Services\ScopeService;

class StudentAffairsController extends Controller
{
    public function index()
    {
        $query = Student::query();
        $query = ScopeService::scopeStudents($query, auth()->user());
        $students = $query->latest()->paginate(15);
        $services = \App\Models\Service::where('is_active', true)->get();

        // Inject resolution data for each student
        $students->getCollection()->transform(function ($student) {
            $student->resolution = \App\Services\TuitionResolverService::resolve($student);
            return $student;
        });

        return view('affairs.student.index', compact('students', 'services'));
    }

    public function create()
    {
        return view('affairs.student.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'national_id'      => 'required|string|unique:students,national_id',
            'reference_number' => 'required|string|unique:students,reference_number',
            'academic_year'    => 'required|string',
            'program'          => 'required|string',
            'phone'            => 'nullable|string|max:20',
            'user_category'    => 'nullable|string',
            'special_category' => 'nullable|string',
        ]);

        $student = Student::create($validated);
        AuditLoggerService::log('Create Student', $student, null, $student->only('name', 'national_id', 'program'));

        return redirect()->route('affairs.student.index')->with('success', 'تم إضافة الطالب بنجاح.');
    }

    public function receipts(Student $student)
    {
        $payments = $student->payments()->with('service')->latest()->get();
        return view('affairs.student.receipts', compact('student', 'payments'));
    }

    /**
     * Import students from CSV with full validation and import report.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:5120',
        ]);

        $file   = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return redirect()->route('affairs.student.index')
                ->withErrors(['file' => 'حدث خطأ أثناء فتح الملف.']);
        }

        // ── Auto-detect delimiter ───────────────────────────────────
        $firstLine = fgets($handle);
        $delimiter = ',';
        if ($firstLine !== false) {
            $commaCount = substr_count($firstLine, ',');
            $semiCount  = substr_count($firstLine, ';');
            $tabCount   = substr_count($firstLine, "\t");

            if ($semiCount > $commaCount && $semiCount > $tabCount) {
                $delimiter = ';';
            } elseif ($tabCount > $commaCount && $tabCount > $semiCount) {
                $delimiter = "\t";
            }
        }
        rewind($handle);

        $imported  = 0;
        $updated   = 0;
        $skipped   = 0;
        $errors    = [];
        $rowNumber = 0;
        $header    = null;

        while (($row = fgetcsv($handle, 4096, $delimiter)) !== false) {
            $rowNumber++;

            // ── First row = header row ──────────────────────────────────
            if ($rowNumber === 1) {
                // Strip UTF-8 BOM (\xEF\xBB\xBF) that Excel adds
                $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);
                // Strip any \r from Windows line endings
                $header = array_map(fn($h) => trim(str_replace("\r", '', $h)), $row);
                continue;
            }

            // Clean, strip \r, and fix encoding
            $row = array_map(function($cell) {
                $cell = trim(str_replace("\r", '', (string)$cell));
                // If text is not valid UTF-8 (e.g. Arabic Windows-1256 from Excel), convert it
                if ($cell !== '' && !mb_check_encoding($cell, 'UTF-8')) {
                    $cell = @iconv('Windows-1256', 'UTF-8//IGNORE', $cell) ?: $cell;
                }
                return $cell;
            }, $row);

            // Skip truly empty rows
            if (empty(array_filter($row))) continue;

            // Pad row to at least 9 columns
            while (count($row) < 9) {
                $row[] = '';
            }

            [$name, $national_id, $reference_number, $academic_year, $program] = [
                $row[0],
                $row[1],
                $row[2],
                $row[3],
                $row[4],
            ];
            $phone            = $row[5] ?? '';
            $user_category    = $row[6] ?: 'Student';
            $special_category = $row[7] ?? '';
            $faculty_id       = is_numeric($row[8] ?? '') ? (int)$row[8] : null;
            $department_id    = is_numeric($row[9] ?? '') ? (int)$row[9] : null;

            // ── Validation ──────────────────────────────────────────────
            $rowErrors = [];
            if ($name           === '') $rowErrors[] = 'الاسم مفقود';
            if ($national_id    === '') $rowErrors[] = 'الرقم القومي مفقود';
            if ($reference_number === '') $rowErrors[] = 'الرقم المرجعي مفقود';
            if ($academic_year  === '') $rowErrors[] = 'الفرقة / السنة الدراسية مفقودة';
            if ($program        === '') $rowErrors[] = 'البرنامج / التخصص مفقود';

            if (!empty($rowErrors)) {
                $errors[] = "صف {$rowNumber}: " . implode('، ', $rowErrors);
                $skipped++;
                continue;
            }

            // ── Upsert Logic (Update or Create) ─────────────────────────
            $student = Student::where('national_id', $national_id)->first();

            if ($student) {
                // Update existing student (Promotion / Adjustment)
                $student->update([
                    'academic_year'    => $academic_year,
                    'program'          => $program,
                    'user_category'    => $user_category ?: 'Student',
                    'special_category' => $special_category ?: null,
                    'faculty_id'       => $faculty_id,
                    'department_id'    => $department_id,
                    // Note: Name, Phone, and Reference Number aren't aggressively overridden 
                    // unless you want them to be. Academic fields are the priority.
                ]);
                $updated++;
            } else {
                // Check reference number uniqueness for NEW students only
                if (Student::where('reference_number', $reference_number)->exists()) {
                    $errors[] = "صف {$rowNumber}: الرقم المرجعي [{$reference_number}] مكرر لطالب آخر — تم التخطي.";
                    $skipped++;
                    continue;
                }

                Student::create([
                    'name'             => $name,
                    'national_id'      => $national_id,
                    'reference_number' => $reference_number,
                    'academic_year'    => $academic_year,
                    'program'          => $program,
                    'phone'            => $phone ?: null,
                    'user_category'    => $user_category ?: 'Student',
                    'special_category' => $special_category ?: null,
                    'faculty_id'       => $faculty_id,
                    'department_id'    => $department_id,
                ]);
                $imported++;
            }
        }
        fclose($handle);

        AuditLoggerService::log('Import Students CSV', null, null, [
            'imported' => $imported,
            'updated'  => $updated,
            'skipped'  => $skipped,
            'errors'   => count($errors),
        ]);

        session(['import_report' => [
            'imported' => $imported,
            'updated'  => $updated,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ]]);

        $msg = "تم استيراد {$imported} طالب جديد، وتحديث/ترقية بيانات {$updated} طالب بنجاح. تم تخطي {$skipped} صفوف.";
        if (!empty($errors)) {
            $msg .= ' راجع تقرير الاستيراد أدناه.';
        }

        return redirect()->route('affairs.student.index')->with('success', $msg);
    }

    /**
     * Process a manual cash payment entry.
     */
    public function manualPay(Request $request, Student $student)
    {
        if (!auth()->user()->hasPermission('manual_cash_entry')) {
            abort(403, 'غير مصرح لك بإدخال دفع يدوي.');
        }

        $validated = $request->validate([
            'service_id'       => 'required|exists:services,id',
            'amount'           => 'required|numeric|min:0',
            'reference_number' => 'nullable|string',
            'notes'            => 'nullable|string|max:500',
        ]);

        $service = \App\Models\Service::findOrFail($validated['service_id']);

        $payment = \App\Models\Payment::create([
            'student_id'       => $student->id,
            'service_id'       => $service->id,
            'amount'           => $validated['amount'],
            'total_amount'     => $validated['amount'],
            'quantity'         => 1,
            'status'           => 'paid',
            'payment_method'   => 'Cash Override',
            'payment_date'     => now(),
            'reference_number' => $validated['reference_number'] ?? ('MAN-' . strtoupper(Str::random(8))),
            'notes'            => 'Manual Entry: ' . $validated['notes'],
            'user_category'    => $student->user_category ?: 'Student',
            'program'          => $student->program,
            'faculty_snapshot' => $student->faculty?->name ?? 'N/A',
            'department_snapshot' => $student->department?->name ?? 'N/A',
        ]);

        AuditLoggerService::log('Manual Cash Entry', $payment, null, [
            'admin_id' => auth()->id(),
            'student'  => $student->name,
            'amount'   => $payment->amount,
            'service'  => $service->name
        ]);

        return back()->with('success', 'تم تسجيل عملية الدفع اليدوي بنجاح.');
    }

    /**
     * Download a ready-to-fill CSV template with the correct format.
     * Includes UTF-8 BOM so Excel opens it properly in Arabic.
     */
    public function csvTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="students_template.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = [
            'name',            // 0 - الاسم (إلزامي)
            'national_id',     // 1 - الرقم القومي (إلزامي)
            'reference_number',// 2 - الرقم المرجعي (إلزامي)
            'academic_year',   // 3 - الفرقة الدراسية (إلزامي)
            'program',         // 4 - التخصص/البرنامج (إلزامي)
            'phone',           // 5 - رقم الهاتف (اختياري)
            'user_category',   // 6 - الفئة: Student أو Graduate (اختياري)
            'special_category',// 7 - الإعفاء الخاص (اختياري)
            'faculty_id',      // 8 - معرف الكلية (اختياري - رقم)
            'department_id',   // 9 - معرف القسم (اختياري - رقم)
        ];

        $sampleRow = [
            'أحمد محمد علي',
            '30001010101010',
            'REF-2024-001',
            'الفرقة الأولى',
            'علوم الحاسب',
            '01012345678',
            'Student',
            '',
            '',
            '',
        ];

        $callback = function () use ($columns, $sampleRow) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM — required for Excel to open Arabic CSV correctly
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, $columns);
            fputcsv($handle, $sampleRow);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
