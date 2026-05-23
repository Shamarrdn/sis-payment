<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Models\Discount;
use App\Models\Faculty;
use App\Models\Announcement;
use App\Notifications\NewPaymentOrDue;
use App\Notifications\NewRequestSubmitted;
use App\Notifications\RequestStatusChanged;
use App\Services\PaymentGatewayService;
use App\Services\StaffNotifier;
use App\Services\TuitionResolverService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentPortalController extends Controller
{
    private $gateway;

    public function __construct(PaymentGatewayService $gateway)
    {
        $this->gateway = $gateway;
    }

    private function getTuitionServices(): array
    {
        return [
            'full'  => Service::where('name', 'like', '%دفع كامل%')->where('is_active', true)->first(),
            'inst1' => Service::where('name', 'like', '%القسط الأول%')->where('is_active', true)->first(),
            'inst2' => Service::where('name', 'like', '%القسط الثاني%')->where('is_active', true)->first(),
        ];
    }

    private function hasPaid(int $studentId, ?Service $service): bool
    {
        if (!$service) return false;
        return Payment::where('student_id', $studentId)
            ->where('service_id', $service->id)
            ->where('status', 'paid')
            ->exists();
    }

    /**
     * Idempotency: generate a unique attempt key and ensure
     * we never create duplicate payments for the same request.
     */
    private function buildIdempotencyKey(int $studentId, int $serviceId, int $quantity): string
    {
        return hash('sha256', implode('|', [
            $studentId,
            $serviceId,
            $quantity,
            now()->format('Y-m-d H:i'), // 1-minute resolution window
        ]));
    }

    private function hasDuplicateAttempt(string $key): bool
    {
        return PaymentAttempt::where('idempotency_key', $key)
            ->where('status', 'verified')
            ->exists();
    }

    private function processPaymentWithIdempotency(
        int $studentId, int $serviceId, int $quantity,
        float $singleAmount, float $totalAmount, string $method, array $extraFields = []
    ): array {
        $key = $this->buildIdempotencyKey($studentId, $serviceId, $quantity);

        if ($this->hasDuplicateAttempt($key)) {
            return ['duplicate' => true];
        }

        // Apply discount if student has a special_category with an active discount
        $student  = Auth::guard('student')->user();
        $discount = Discount::forCategory($student->special_category ?? null);
        $discountedAmount = $discount ? $discount->applyTo($totalAmount) : $totalAmount;
        $discountNote = $discount ? ' (بعد خصم ' . $discount->name . ')' : '';

        $result = $this->gateway->processPayment($discountedAmount, $method);

        $payment = Payment::create(array_merge([
            'student_id'       => $studentId,
            'service_id'       => $serviceId,
            'amount'           => $singleAmount,
            'quantity'         => $quantity,
            'total_amount'     => $discountedAmount,
            'payment_method'   => $method,
            'reference_number' => $result['reference_number'],
            'payment_date'     => now(),
            'status'           => $result['status'],
            'discount_amount'  => ($resolution['discount_amount'] ?? 0),
        ], $extraFields, [
            'notes' => ($extraFields['notes'] ?? '') . $discountNote,
        ]));

        PaymentAttempt::create([
            'payment_id'       => $payment->id,
            'idempotency_key'  => $key,
            'gateway_tx_id'    => $result['reference_number'],
            'status'           => $result['status'] === 'paid' ? 'verified' : 'failed',
            'payload'          => $result,
        ]);

        return ['payment' => $payment, 'status' => $result['status']];
    }

    public function dashboard()
    {
        $student  = Auth::guard('student')->user()->load(['faculty', 'department']);
        $tuition  = $this->getTuitionServices();

        $mostUsedIds = Payment::where('status', 'paid')
            ->whereHas('service', fn($q) => $q->whereNotIn('type', ['مصاريف دراسية', 'مصروفات دراسية']))
            ->select('service_id', DB::raw('count(*) as total'))
            ->groupBy('service_id')
            ->orderByDesc('total')
            ->limit(4)
            ->pluck('service_id');

        $mostUsed = Service::whereIn('id', $mostUsedIds)->get();
        $services = Service::whereNotIn('type', ['مصاريف دراسية', 'مصروفات دراسية'])->where('is_active', true)->get()->groupBy('type');

        $paidFull  = $this->hasPaid($student->id, $tuition['full']);
        $paidInst1 = $this->hasPaid($student->id, $tuition['inst1']);
        $paidInst2 = $this->hasPaid($student->id, $tuition['inst2']);

        $resolution = TuitionResolverService::resolve($student);

        $unpaidCount = Payment::where('student_id', $student->id)->where('status', 'pending')->count();
        $recentPayments = Payment::where('student_id', $student->id)->with('service')->orderBy('payment_date', 'desc')->take(5)->get();

        $announcements = Announcement::visibleToStudent($student)->latest()->take(3)->get();
        $unreadNotifications = $student->unreadNotifications()->count();

        $iconMap = [
            'شئون طلاب' => [
                'icon' => 'bi-person-badge-fill',
                'bg' => '#e0f2f1',
                'color' => '#00695c'
            ],
            'شئون طلبة' => [
                'icon' => 'bi-person-badge-fill',
                'bg' => '#e0f2f1',
                'color' => '#00695c'
            ],
            'التماسات' => [
                'icon' => 'bi-file-earmark-text-fill',
                'bg' => '#fff3e0',
                'color' => '#e65100'
            ],
            'خريجين' => [
                'icon' => 'bi-mortarboard-fill',
                'bg' => '#fce4ec',
                'color' => '#c2185b'
            ],
            'سمر كورس' => [
                'icon' => 'bi-sun-fill',
                'bg' => '#fff9c4',
                'color' => '#fbc02d'
            ],
            'خدمات عامة' => [
                'icon' => 'bi-gear-fill',
                'bg' => '#f5f5f5',
                'color' => '#424242'
            ],
            'أخرى' => [
                'icon' => 'bi-grid-fill',
                'bg' => '#f3e5f5',
                'color' => '#7b1fa2'
            ]
        ];

        return view('student.dashboard', compact('services', 'mostUsed', 'paidFull', 'paidInst1', 'paidInst2', 'tuition', 'unpaidCount', 'recentPayments', 'resolution', 'iconMap', 'announcements', 'unreadNotifications'));
    }

    public function profile()
    {
        $student   = Auth::guard('student')->user()->load(['documents', 'sensitiveDataRequests' => function($q) {
            $q->where('status', 'pending');
        }]);
        $faculties = Faculty::where('is_active', true)->with('activeDepartments')->get();
        $pendingRequest = $student->sensitiveDataRequests->first();

        return view('student.profile', compact('student', 'faculties', 'pendingRequest'));
    }

    public function updateProfile(Request $request)
    {
        $student = Auth::guard('student')->user();

        $rules = [
            'name'           => 'required|string|max:255',
            'national_id'    => 'required|string|max:30|unique:students,national_id,' . $student->id,
            'phone'          => 'nullable|string|max:25',
            'email'          => 'nullable|email|max:255|unique:students,email,' . $student->id,
            'address'        => 'nullable|string|max:255',
            'academic_year'  => 'nullable|string|max:100',
            'program'        => 'nullable|string|max:255',
            'faculty_id'     => 'nullable|exists:faculties,id',
            'department_id'  => 'nullable|exists:departments,id',
            'special_category' => 'nullable|string|max:255',
            'user_category'  => 'nullable|string|max:255',
        ];

        $validated = $request->validate($rules);

        if (!empty($validated['department_id']) && !empty($validated['faculty_id'])) {
            $department = \App\Models\Department::find($validated['department_id']);
            if ($department && $department->faculty_id !== (int)$validated['faculty_id']) {
                return back()->withErrors(['department_id' => 'القسم المحدد لا يتبع الكلية المحددة.'])->withInput();
            }
        }

        // Detect sensitive changes: name, national_id, faculty_id, department_id
        $sensitiveChanges = [];
        
        if ($validated['name'] !== $student->name) {
            $sensitiveChanges['name'] = $validated['name'];
        }
        if ($validated['national_id'] !== $student->national_id) {
            $sensitiveChanges['national_id'] = $validated['national_id'];
        }
        
        $reqFacultyId = !empty($validated['faculty_id']) ? (int)$validated['faculty_id'] : null;
        $currFacultyId = $student->faculty_id ? (int)$student->faculty_id : null;
        if ($reqFacultyId !== $currFacultyId) {
            $sensitiveChanges['faculty_id'] = $reqFacultyId;
        }

        $reqDeptId = !empty($validated['department_id']) ? (int)$validated['department_id'] : null;
        $currDeptId = $student->department_id ? (int)$student->department_id : null;
        if ($reqDeptId !== $currDeptId) {
            $sensitiveChanges['department_id'] = $reqDeptId;
        }

        $hasSensitiveChanges = !empty($sensitiveChanges);

        if ($hasSensitiveChanges) {
            // Store sensitive request
            \App\Models\SensitiveDataRequest::updateOrCreate(
                ['student_id' => $student->id, 'status' => 'pending'],
                [
                    'requested_data' => $sensitiveChanges,
                    'rejection_reason' => null
                ]
            );

            StaffNotifier::notifyRoles(
                ['student_affairs', 'super_admin', 'admin'],
                new NewRequestSubmitted([
                    'title'        => 'طلب تعديل بيانات حساسة',
                    'message'      => $student->name . ' — طلب تعديل بيانات حساسة',
                    'student_name' => $student->name,
                    'request_type' => 'sensitive_data',
                    'request_id'   => $student->id,
                    'action_url'   => route('affairs.student.show', $student),
                ])
            );

            // Revert these fields from the immediate update array
            unset($validated['name']);
            unset($validated['national_id']);
            unset($validated['faculty_id']);
            unset($validated['department_id']);
        }

        // Update other fields directly
        $student->update($validated);

        if ($hasSensitiveChanges) {
            return redirect()->route('student.profile')->with('success', 'تم حفظ بياناتك الشخصية بنجاح، وتم إرسال طلب تعديل البيانات الحساسة (الاسم/الرقم القومي/الكلية/القسم) للمراجعة والاعتماد من قبل إدارة شؤون الطلاب.');
        }

        return redirect()->route('student.profile')->with('success', 'تم حفظ البيانات بنجاح.');
    }

    public function uploadDocument(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:national_id,birth_certificate,personal_photo,additional',
            'file' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->store('documents');

        $student = Auth::guard('student')->user();

        \App\Models\StudentDocument::updateOrCreate(
            ['student_id' => $student->id, 'type' => $validated['type']],
            [
                'file_path' => $path,
                'status' => 'pending',
                'rejection_reason' => null,
            ]
        );

        return back()->with('success', 'تم رفع المستند بنجاح وهو قيد المراجعة حالياً.');
    }

    public function viewDocument(\App\Models\StudentDocument $document)
    {
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            $studentQuery = \App\Models\Student::where('id', $document->student_id);
            $scopedStudents = \App\Services\ScopeService::scopeStudents($studentQuery, $user);
            if (!$scopedStudents->exists()) {
                abort(403, 'غير مصرح لك باستعراض مستندات هذا الطالب.');
            }
        } elseif (Auth::guard('student')->check()) {
            if (Auth::guard('student')->id() !== $document->student_id) {
                abort(403, 'غير مصرح لك باستعراض هذا المستند.');
            }
        } else {
            abort(401);
        }

        $path = \Illuminate\Support\Facades\Storage::disk('local')->path($document->file_path);
        if (!file_exists($path)) {
            abort(404, 'المستند غير موجود على الخادم.');
        }

        return response()->file($path);
    }

    public function tuition()
    {
        $student  = Auth::guard('student')->user();
        $services = $this->getTuitionServices();

        $paidFull  = $this->hasPaid($student->id, $services['full']);
        $paidInst1 = $this->hasPaid($student->id, $services['inst1']);
        $paidInst2 = $this->hasPaid($student->id, $services['inst2']);

        if ($paidFull || ($paidInst1 && $paidInst2)) {
            return redirect()->route('student.dashboard')
                ->with('success', 'لقد سبق لك سداد المصاريف الدراسية بالكامل.');
        }

        // Use the new Dynamic Resolver
        $resolution = TuitionResolverService::resolve($student);

        return view('student.tuition', compact('services', 'paidFull', 'paidInst1', 'paidInst2', 'resolution'));
    }

    public function processTuition(Request $request)
    {
        $student = Auth::guard('student')->user();
        $tuition = $this->getTuitionServices();

        $validated = $request->validate([
            'choice'         => 'required|in:full,inst1,inst2',
            'payment_method' => 'required|string|in:Visa,Fawry',
        ]);

        $choice = $validated['choice'];
        $method = $validated['payment_method'];

        $paidFull  = $this->hasPaid($student->id, $tuition['full']);
        $paidInst1 = $this->hasPaid($student->id, $tuition['inst1']);
        $paidInst2 = $this->hasPaid($student->id, $tuition['inst2']);

        if ($choice === 'inst2' && !$paidInst1) {
            return back()->withErrors(['choice' => 'يجب سداد القسط الأول أولاً قبل دفع القسط الثاني.']);
        }
        if (($choice === 'full' && $paidFull) || ($choice === 'inst1' && $paidInst1) || ($choice === 'inst2' && $paidInst2)) {
            return back()->withErrors(['choice' => 'لقد سبق لك سداد هذا الخيار بالفعل.']);
        }

        $service = $tuition[$choice];
        if (!$service) return back()->withErrors(['choice' => 'الخدمة غير موجودة أو غير مفعلة حالياً.']);

        // Resolve Dynamic Amount
        $resolution = TuitionResolverService::resolve($student);
        $baseAmount = $service->amount;
        $totalAmount = $baseAmount;
        $notes = 'سداد مصروفات - ' . $resolution['resolved_by'];

        if ($choice === 'full') {
            $totalAmount = $resolution['total'];
            $notes .= ' (دفع كامل)';
        } elseif ($choice === 'inst1') {
            $totalAmount = $resolution['inst1_amount'];
            $notes .= ' (القسط الأول - ' . number_format($totalAmount) . ' ج.م)';
        } elseif ($choice === 'inst2') {
            $totalAmount = $resolution['inst2_amount'];
            $notes .= ' (القسط الثاني - ' . number_format($totalAmount) . ' ج.م)';
        }

        $outcome = $this->processPaymentWithIdempotency(
            $student->id, $service->id, 1, $baseAmount, $totalAmount, $method,
            [
                'notes'               => $notes,
                'faculty_id'          => $student->faculty_id,
                'department_id'       => $student->department_id,
                'faculty_snapshot'    => $student->facultyName(),
                'department_snapshot' => $student->departmentName(),
                'academic_year'       => $student->academic_year,
                'program'             => $student->program,
                'user_category'       => $student->user_category,
            ]
        );

        if (isset($outcome['duplicate'])) {
            return back()->withErrors(['choice' => 'يبدو أن عملية مماثلة تمت معالجتها مسبقاً في نفس الدقيقة. يرجى مراجعة الأرشيف الرقمي.']);
        }

        if ($outcome['status'] === 'paid') {
            $payment = $outcome['payment'];
            $student->notify(new NewPaymentOrDue([
                'title'            => 'تم سداد المصاريف الدراسية',
                'message'          => 'تم تأكيد سداد المصاريف الدراسية',
                'amount'           => $payment->total_amount,
                'reference_number' => $payment->reference_number,
                'action_url'       => route('student.receipt', $payment),
            ]));

            return redirect()->route('student.receipt', $payment)->with('success', 'تم سداد المصاريف بنجاح.');
        }

        return redirect()->route('student.history')->with('error', 'فشلت عملية الدفع. يرجى المحاولة مرة أخرى أو التواصل مع الشؤون المالية.');
    }

    public function checkout(Service $service)
    {
        if (!$service->is_active) {
            return redirect()->route('student.dashboard')
                ->with('error', 'هذه الخدمة غير متاحة حالياً. يرجى التواصل مع الإدارة.');
        }
        return view('student.checkout', compact('service'));
    }

    public function pay(Request $request, Service $service)
    {
        if (!$service->is_active) {
            return redirect()->route('student.dashboard')
                ->with('error', 'هذه الخدمة غير متاحة حالياً.');
        }

        $student = Auth::guard('student')->user();

        $rules = ['payment_method' => 'required|string|in:Visa,Fawry'];

        if ($service->allows_quantity) {
            $rules['quantity'] = 'required|integer|min:1|max:10';
        }
        if (!empty($service->sub_options)) {
            $rules['notes'] = ['required', 'string', \Illuminate\Validation\Rule::in($service->sub_options)];
        } elseif ($service->requires_subject) {
            $rules['notes'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);
        $quantity    = $validated['quantity'] ?? 1;
        $totalAmount = $service->amount * $quantity;
        $method      = $validated['payment_method'];

        $outcome = $this->processPaymentWithIdempotency(
            $student->id, $service->id, $quantity, $service->amount, $totalAmount, $method,
            [
                'notes'               => $validated['notes'] ?? null,
                'faculty_id'          => $student->faculty_id,
                'department_id'       => $student->department_id,
                'faculty_snapshot'    => $student->facultyName(),
                'department_snapshot' => $student->departmentName(),
                'academic_year'       => $student->academic_year,
                'program'             => $student->program,
                'user_category'       => $student->user_category,
            ]
        );

        if (isset($outcome['duplicate'])) {
            return back()->with('error', 'تم اكتشاف طلب مكرر. يرجى مراجعة الأرشيف الرقمي قبل إعادة المحاولة.');
        }

        if ($outcome['status'] === 'paid') {
            $payment = $outcome['payment'];
            $student->notify(new NewPaymentOrDue([
                'title'            => 'تم تأكيد الدفع',
                'message'          => 'تم سداد ' . ($service->name ?? 'الخدمة') . ' بنجاح',
                'amount'           => $payment->total_amount,
                'service_name'     => $service->name,
                'reference_number' => $payment->reference_number,
                'action_url'       => route('student.receipt', $payment),
            ]));

            return redirect()->route('student.receipt', $payment)
                ->with('success', 'تم الدفع بنجاح عبر ' . $method . '. رقم الإيصال: ' . $payment->reference_number);
        }

        return redirect()->route('student.history')
            ->with('error', 'فشلت عملية الدفع عبر ' . $method . '. يرجى المحاولة مرة أخرى أو اختيار وسيلة دفع مختلفة.');
    }

    public function receipt(Payment $payment)
    {
        $studentId = Auth::guard('student')->id();
        if ($payment->student_id !== $studentId) {
            abort(403, 'غير مصرح لك بعرض هذا الإيصال.');
        }
        if ($payment->status !== 'paid') {
            return redirect()->route('student.history')
                ->with('error', 'الإيصال غير متاح لأن العملية لم تكتمل بنجاح.');
        }
        return view('student.receipt', compact('payment'));
    }

    public function history()
    {
        $student  = Auth::guard('student')->user();
        $payments = Payment::where('student_id', $student->id)
            ->with('service')
            ->orderBy('payment_date', 'desc')
            ->paginate(15);

        return view('student.history', compact('payments'));
    }
}
