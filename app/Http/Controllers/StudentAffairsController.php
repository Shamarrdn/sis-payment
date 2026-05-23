<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Student;
use App\Services\AuditLoggerService;
use App\Services\ScopeService;

class StudentAffairsController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::query();
        $query = ScopeService::scopeStudents($query, auth()->user());

        // Advanced Search & Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhere('national_id', 'like', "%{$search}%")
                  ->orWhere('academic_year', 'like', "%{$search}%")
                  ->orWhere('program', 'like', "%{$search}%");
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

        if ($request->filled('completion')) {
            $completion = $request->completion;
            if ($completion === 'complete') {
                $query->whereNotNull('phone')->where('phone', '!=', '')
                    ->whereNotNull('email')->where('email', '!=', '')
                    ->whereNotNull('address')->where('address', '!=', '')
                    ->whereHas('documents', function($q) {
                        $q->where('type', 'national_id')->where('status', '!=', 'rejected');
                    })
                    ->whereHas('documents', function($q) {
                        $q->where('type', 'birth_certificate')->where('status', '!=', 'rejected');
                    })
                    ->whereHas('documents', function($q) {
                        $q->where('type', 'personal_photo')->where('status', '!=', 'rejected');
                    });
            } elseif ($completion === 'incomplete') {
                $query->where(function($q) {
                    $q->whereNull('phone')->orWhere('phone', '')
                      ->orWhereNull('email')->orWhere('email', '')
                      ->orWhereNull('address')->orWhere('address', '')
                      ->orWhereNotExists(function($sub) {
                          $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('student_documents')
                              ->whereColumn('student_documents.student_id', 'students.id')
                              ->where('type', 'national_id')
                              ->where('status', '!=', 'rejected');
                      })
                      ->orWhereNotExists(function($sub) {
                          $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('student_documents')
                              ->whereColumn('student_documents.student_id', 'students.id')
                              ->where('type', 'birth_certificate')
                              ->where('status', '!=', 'rejected');
                      })
                      ->orWhereNotExists(function($sub) {
                          $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('student_documents')
                              ->whereColumn('student_documents.student_id', 'students.id')
                              ->where('type', 'personal_photo')
                              ->where('status', '!=', 'rejected');
                      });
                });
            }
        }

        if ($request->filled('missing_document')) {
            $missingDoc = $request->missing_document;
            if (in_array($missingDoc, ['national_id', 'birth_certificate', 'personal_photo'])) {
                $query->whereNotExists(function($sub) use ($missingDoc) {
                    $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                        ->from('student_documents')
                        ->whereColumn('student_documents.student_id', 'students.id')
                        ->where('type', $missingDoc)
                        ->where('status', '!=', 'rejected');
                });
            }
        }

        $students = $query->latest()->paginate(15);
        $services = \App\Models\Service::where('is_active', true)->get();
        $faculties = \App\Models\Faculty::where('is_active', true)->with('activeDepartments')->get();

        // Inject resolution data for each student
        $students->getCollection()->transform(function ($student) {
            $student->resolution = \App\Services\TuitionResolverService::resolve($student);
            return $student;
        });

        return view('affairs.student.index', compact('students', 'services', 'faculties'));
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

    public function show(Student $student)
    {
        $student->load([
            'documents',
            'sensitiveDataRequests' => function ($q) {
                $q->latest();
            },
            'statusHistories.changer',
            'internalNotes.user'
        ]);

        $faculties = \App\Models\Faculty::where('is_active', true)->with('activeDepartments')->get();

        return view('affairs.student.show', compact('student', 'faculties'));
    }

    public function updateStatus(Student $student, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,suspended,graduated',
            'notes' => 'required|string|max:500',
        ]);

        $oldStatus = $student->status;
        $newStatus = $validated['status'];

        if ($oldStatus === $newStatus) {
            return back()->withErrors(['status' => 'الحالة المحددة مطابقة للحالة الحالية للطالب.']);
        }

        $student->update(['status' => $newStatus]);

        $student->statusHistories()->create([
            'status' => $newStatus,
            'changed_by' => auth()->id(),
            'notes' => $validated['notes'],
        ]);

        AuditLoggerService::log('Update Student Status', $student, ['status' => $oldStatus], ['status' => $newStatus, 'notes' => $validated['notes']]);

        return back()->with('success', 'تم تحديث حالة الطالب بنجاح وتسجيل العملية في سجل التتبع.');
    }

    public function addNote(Student $student, Request $request)
    {
        $validated = $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        $student->internalNotes()->create([
            'user_id' => auth()->id(),
            'note' => $validated['note'],
        ]);

        AuditLoggerService::log('Add Student Internal Note', $student, null, ['note_preview' => Str::limit($validated['note'], 50)]);

        return back()->with('success', 'تم إضافة الملاحظة الداخلية بنجاح.');
    }

    public function verifyDocument(\App\Models\StudentDocument $document, Request $request)
    {
        $user = auth()->user();
        
        $studentQuery = Student::where('id', $document->student_id);
        $scopedStudents = \App\Services\ScopeService::scopeStudents($studentQuery, $user);
        if (!$scopedStudents->exists()) {
            abort(403, 'غير مصرح لك بالوصول لهذا الطالب أو تعديل مستنداته.');
        }

        $validated = $request->validate([
            'status' => 'required|in:verified,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:255',
        ]);

        $oldStatus = $document->status;
        $document->update([
            'status' => $validated['status'],
            'rejection_reason' => $validated['status'] === 'rejected' ? $validated['rejection_reason'] : null,
        ]);

        $student = $document->student;
        AuditLoggerService::log('Verify Document', $document, ['status' => $oldStatus], ['status' => $validated['status'], 'rejection_reason' => $validated['rejection_reason'] ?? null]);

        return back()->with('success', 'تم تحديث حالة المستند بنجاح.');
    }

    public function processSensitiveRequest(\App\Models\SensitiveDataRequest $updateRequest, Request $request)
    {
        $user = auth()->user();
        
        $studentQuery = Student::where('id', $updateRequest->student_id);
        $scopedStudents = \App\Services\ScopeService::scopeStudents($studentQuery, $user);
        if (!$scopedStudents->exists()) {
            abort(403, 'غير مصرح لك بالوصول لهذا الطالب أو تعديل بياناته.');
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:255',
        ]);

        $student = $updateRequest->student;
        $oldValues = $student->only(array_keys($updateRequest->requested_data));

        if ($validated['status'] === 'approved') {
            $student->update($updateRequest->requested_data);

            $updateRequest->update([
                'status' => 'approved',
                'reviewed_by' => $user->id,
            ]);

            AuditLoggerService::log('Approve Sensitive Data Request', $student, $oldValues, $updateRequest->requested_data);
        } else {
            $updateRequest->update([
                'status' => 'rejected',
                'reviewed_by' => $user->id,
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            AuditLoggerService::log('Reject Sensitive Data Request', $student, null, [
                'request_id' => $updateRequest->id,
                'rejection_reason' => $validated['rejection_reason'],
            ]);
        }

        return back()->with('success', $validated['status'] === 'approved' ? 'تم اعتماد تعديل البيانات الحساسة وتطبيقها بنجاح.' : 'تم رفض طلب تعديل البيانات الحساسة.');
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'action_type' => 'required|in:update_status,add_note,verify_documents',
            'status' => 'required_if:action_type,update_status|in:active,suspended,graduated',
            'status_notes' => 'required_if:action_type,update_status|string|max:500',
            'note' => 'required_if:action_type,add_note|string|max:1000',
        ]);

        $user = auth()->user();
        $studentIds = $validated['student_ids'];

        $studentQuery = Student::whereIn('id', $studentIds);
        $scopedQuery = \App\Services\ScopeService::scopeStudents($studentQuery, $user);
        $validStudentIds = $scopedQuery->pluck('id')->toArray();

        if (count($studentIds) !== count($validStudentIds)) {
            return back()->withErrors(['student_ids' => 'تحتوي القائمة المحددة على طلاب خارج نطاق صلاحيتك.'])->withInput();
        }

        $processedCount = 0;

        if ($validated['action_type'] === 'update_status') {
            $newStatus = $validated['status'];
            $statusNotes = $validated['status_notes'];

            foreach ($validStudentIds as $id) {
                $student = Student::find($id);
                $oldStatus = $student->status;
                if ($oldStatus !== $newStatus) {
                    $student->update(['status' => $newStatus]);
                    $student->statusHistories()->create([
                        'status' => $newStatus,
                        'changed_by' => $user->id,
                        'notes' => $statusNotes,
                    ]);
                    AuditLoggerService::log('Bulk Update Student Status', $student, ['status' => $oldStatus], ['status' => $newStatus, 'notes' => $statusNotes]);
                    $processedCount++;
                }
            }
            $message = "تم تحديث حالة {$processedCount} من الطلاب المحددين بنجاح.";

        } elseif ($validated['action_type'] === 'add_note') {
            $noteText = $validated['note'];

            foreach ($validStudentIds as $id) {
                $student = Student::find($id);
                $student->internalNotes()->create([
                    'user_id' => $user->id,
                    'note' => $noteText,
                ]);
                AuditLoggerService::log('Bulk Add Student Note', $student, null, ['note_preview' => Str::limit($noteText, 50)]);
                $processedCount++;
            }
            $message = "تم إضافة ملاحظة داخلية لـ {$processedCount} من الطلاب بنجاح.";

        } elseif ($validated['action_type'] === 'verify_documents') {
            $pendingDocuments = \App\Models\StudentDocument::whereIn('student_id', $validStudentIds)
                ->where('status', 'pending')
                ->get();

            foreach ($pendingDocuments as $doc) {
                $oldStatus = $doc->status;
                $doc->update(['status' => 'verified']);
                AuditLoggerService::log('Bulk Verify Document', $doc, ['status' => $oldStatus], ['status' => 'verified']);
                $processedCount++;
            }
            $message = "تم اعتماد وقبول {$processedCount} من المستندات المعلقة للطلاب بنجاح.";
        }

        return back()->with('success', $message);
    }
}
