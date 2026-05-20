<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Service;
use App\Models\Payment;
use App\Models\Student;
use App\Models\AuditLog;
use App\Models\Faculty;
use App\Services\AuditLoggerService;
use App\Services\ScopeService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        
        // Apply Scopes
        $stuQuery = ScopeService::scopeStudents(Student::query(), $user);
        $payQuery = ScopeService::scopePayments(Payment::query(), $user);

        $stats = [
            'total_students'   => (clone $stuQuery)->count(),
            'total_payments'   => (clone $payQuery)->where('status', 'paid')->count(),
            'total_revenue'    => (clone $payQuery)->where('status', 'paid')->sum('total_amount'),
            'total_employees'  => User::count(),
            'today_payments'   => (clone $payQuery)->where('status', 'paid')->whereDate('payment_date', today())->count(),
            'pending_payments' => (clone $payQuery)->where('status', 'pending')->count(),
            'refund_requests'  => (clone $payQuery)->where('refund_status', 'requested')->count(),
        ];

        // Faculty breakdown (for Super Admin or to show assigned faculty stats specifically)
        $faculties = Faculty::where('is_active', true)->get();
        $facultyStats = $faculties->map(function($f) {
            return [
                'name'     => $f->name,
                'code'     => $f->code,
                'students' => Student::where('faculty_id', $f->id)->count(),
                'revenue'  => Payment::where('faculty_id', $f->id)->where('status', 'paid')->sum('total_amount'),
            ];
        });

        $query = (clone $payQuery)->with(['student', 'service'])->orderByDesc('payment_date');

        // Advanced Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%$search%")
                  ->orWhereHas('student', fn($sq) =>
                      $sq->where('name', 'like', "%$search%")
                         ->orWhere('national_id', 'like', "%$search%")
                  );
            });
        }
        if ($request->filled('program'))       $query->where('program', 'like', "%{$request->program}%");
        if ($request->filled('faculty_id'))    $query->where('faculty_id', $request->faculty_id);
        if ($request->filled('status'))        $query->where('status', $request->status);
        if ($request->filled('refund_status')) $query->where('refund_status', $request->refund_status);
        if ($request->filled('user_category')) $query->where('user_category', $request->user_category);
        if ($request->filled('date_from'))     $query->whereDate('payment_date', '>=', $request->date_from);
        if ($request->filled('date_to'))       $query->whereDate('payment_date', '<=', $request->date_to);

        $payments = $query->paginate(20)->withQueryString();

        return view('admin.dashboard', compact('stats', 'payments', 'facultyStats', 'faculties'));
    }

    // ─── Employees ────────────────────────────────────────────────────
    public function employees()
    {
        $employees = User::with('assignment.faculty')->get();
        $faculties = Faculty::where('is_active', true)->with('activeDepartments')->get();
        return view('admin.users.index', compact('employees', 'faculties'));
    }

    public function storeEmployee(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role'     => ['required', Rule::in(['student_affairs', 'financial_affairs', 'graduate_affairs', 'super_admin'])],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        AuditLoggerService::log('Create Employee', $user, null, $user->only('name', 'email', 'role'));
        return back()->with('success', 'تم إضافة الموظف بنجاح.');
    }

    public function updateEmployee(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role'  => ['required', Rule::in(['student_affairs', 'financial_affairs', 'graduate_affairs', 'super_admin'])],
        ]);

        $old = $user->only('name', 'email', 'role');

        $user->update(['name' => $validated['name'], 'email' => $validated['email'], 'role' => $validated['role']]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            $user->password = Hash::make($request->password);
            $user->save();
        }

        AuditLoggerService::log('Update Employee', $user, $old, $user->only('name', 'email', 'role'));
        return back()->with('success', 'تم تحديث بيانات الموظف بنجاح.');
    }

    public function deleteEmployee(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'لا يمكنك حذف حسابك الخاص.']);
        }
        AuditLoggerService::log('Delete Employee', $user, $user->only('name', 'email', 'role'));
        $user->delete(); // Soft delete
        return back()->with('success', 'تم حذف الموظف (سيظل محفوظاً في الأرشيف).');
    }

    public function toggleEmployee(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'لا يمكنك تعطيل حسابك الخاص.']);
        }
        
        $oldState = $user->is_active;
        $user->update(['is_active' => !$oldState]);
        
        $actionName = !$oldState ? 'Activate Employee' : 'Deactivate Employee';
        AuditLoggerService::log($actionName, $user, ['is_active' => $oldState], ['is_active' => !$oldState]);
        
        $msg = !$oldState ? 'تم تفعيل حساب الموظف بنجاح.' : 'تم تعطيل حساب الموظف. لن يتمكن من تسجيل الدخول.';
        return back()->with('success', $msg);
    }

    // ─── Services ─────────────────────────────────────────────────────
    public function services()
    {
        $user = auth()->user();
        $query = Service::query();
        
        if ($user->role !== 'super_admin' && $user->role !== 'financial_affairs') {
            $facultyId = $user->assignment?->faculty_id;
            if ($facultyId) {
                $query->where(function($q) use ($facultyId) {
                    $q->where('faculty_id', $facultyId)->orWhereNull('faculty_id');
                });
            }
        }

        if ($user->role === 'student_affairs') {
            $query->where(function($q) {
                $q->where('applicable_to', '!=', 'Graduate')->orWhereNull('applicable_to');
            });
        } elseif ($user->role === 'graduate_affairs') {
            $query->where('applicable_to', 'Graduate');
        }

        $services = $query->get();
        $faculties = Faculty::where('is_active', true)->get();
        return view('admin.services.index', compact('services', 'faculties'));
    }

    public function storeService(Request $request)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'financial_affairs'])) {
            abort(403, 'غير مصرح لك بإنشاء أو تعديل تفاصيل الخدمات. يمكنك فقط إيقاف أو تفعيل الخدمة.');
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'amount'   => 'required|numeric|min:0',
            'type'     => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
        ]);

        $validated['is_active']       = $request->has('is_active');
        $validated['allows_quantity'] = $request->has('allows_quantity');
        $validated['faculty_id']     = $request->faculty_id ?: null;
        $validated['applicable_to']   = $request->applicable_to ?: null;

        $service = Service::create($validated);
        AuditLoggerService::log('Create Service', $service, null, $service->only('name', 'type', 'amount', 'is_active', 'faculty_id'));
        return back()->with('success', 'تم إضافة الخدمة بنجاح.');
    }

    public function updateService(Request $request, Service $service)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'financial_affairs'])) {
            abort(403, 'غير مصرح لك بإنشاء أو تعديل تفاصيل الخدمات. يمكنك فقط إيقاف أو تفعيل الخدمة.');
        }

        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type'   => 'required|string|max:255',
        ]);

        $old = $service->only('name', 'type', 'amount', 'is_active', 'allows_quantity', 'faculty_id', 'applicable_to');

        $service->update([
            'name'            => $validated['name'],
            'amount'          => $validated['amount'],
            'type'            => $validated['type'],
            'category'        => $request->category,
            'is_active'       => $request->has('is_active'),
            'allows_quantity' => $request->has('allows_quantity'),
            'faculty_id'     => $request->faculty_id ?: null,
            'applicable_to'   => $request->applicable_to ?: null,
        ]);

        AuditLoggerService::log('Update Service', $service, $old, $service->only('name', 'type', 'amount', 'is_active', 'faculty_id', 'applicable_to'));
        return back()->with('success', 'تم تحديث الخدمة بنجاح.');
    }

    public function toggleService(Request $request, Service $service)
    {
        $oldState = $service->is_active;
        $service->update(['is_active' => !$oldState]);
        
        $actionName = !$oldState ? 'Activate Service' : 'Pause Service';
        AuditLoggerService::log($actionName, $service, ['is_active' => $oldState], ['is_active' => !$oldState]);
        
        return back()->with('success', 'تم تغيير حالة الخدمة (تفعيل/إيقاف) بنجاح.');
    }

    // ─── Refund Workflow ───────────────────────────────────────────────
    public function refundQueue(Request $request)
    {
        $query = Payment::with(['student', 'service'])
            ->whereIn('refund_status', ['requested', 'approved'])
            ->orderByDesc('updated_at');

        if ($request->filled('refund_status')) {
            $query->where('refund_status', $request->refund_status);
        }

        $payments = $query->paginate(20)->withQueryString();
        return view('admin.refunds.index', compact('payments'));
    }

    public function approveRefund(Payment $payment)
    {
        if (!auth()->user()->hasPermission('approve_refunds')) {
            abort(403, 'غير مصرح لك باعتماد طلبات الاسترداد.');
        }

        if (!in_array($payment->refund_status, ['requested'])) {
            return back()->withErrors(['error' => 'لا يمكن اعتماد هذا الطلب في حالته الحالية.']);
        }

        $old = ['refund_status' => $payment->refund_status];
        $payment->update(['refund_status' => 'approved']);
        AuditLoggerService::log('Approve Refund', $payment, $old, ['refund_status' => 'approved']);

        return back()->with('success', 'تمت الموافقة على طلب الاسترداد.');
    }

    public function processRefund(Request $request, Payment $payment)
    {
        if (!auth()->user()->hasPermission('approve_refunds')) {
            abort(403, 'غير مصرح لك بتنفيذ الاسترداد.');
        }

        $request->validate([
            'refund_reason' => 'required|string|max:500',
        ]);

        if (!in_array($payment->refund_status, ['requested', 'approved'])) {
            return back()->withErrors(['error' => 'لا يمكن تنفيذ الاسترداد في حالته الحالية.']);
        }

        $old = $payment->only('status', 'refund_status', 'refunded_amount');

        $payment->update([
            'refund_status'   => 'refunded',
            'refund_reason'   => $request->refund_reason,
            'refunded_amount' => $payment->total_amount,
            'status'          => 'cancelled',
        ]);

        AuditLoggerService::log('Process Refund', $payment, $old, [
            'refund_status'   => 'refunded',
            'refunded_amount' => $payment->total_amount,
            'reason'          => $request->refund_reason,
        ]);

        return back()->with('success', 'تم تنفيذ الاسترداد وتحديث حالة العملية.');
    }

    public function requestRefund(Request $request, Payment $payment)
    {
        $request->validate(['refund_reason' => 'required|string|max:500']);

        if ($payment->status !== 'paid') {
            return back()->withErrors(['error' => 'يمكن طلب استرداد العمليات الناجحة فقط.']);
        }

        $old = ['refund_status' => $payment->refund_status];
        $payment->update([
            'refund_status' => 'requested',
            'refund_reason' => $request->refund_reason,
        ]);

        AuditLoggerService::log('Request Refund', $payment, $old, [
            'refund_status' => 'requested',
            'reason'        => $request->refund_reason,
        ]);

        return back()->with('success', 'تم تسجيل طلب الاسترداد بنجاح. في انتظار الموافقة الإدارية.');
    }

    public function cancelPayment(Request $request, Payment $payment)
    {
        if (!auth()->user()->hasPermission('cancel_payments')) {
            abort(403, 'غير مصرح لك بإلغاء العمليات.');
        }

        $request->validate(['cancel_reason' => 'required|string|max:500']);

        if (!in_array($payment->status, ['pending'])) {
            return back()->withErrors(['error' => 'يمكن إلغاء العمليات في حالة الانتظار (Pending) فقط.']);
        }

        $old = ['status' => $payment->status];
        $payment->update([
            'status'        => 'cancelled',
            'refund_status' => 'cancelled',
            'refund_reason' => $request->cancel_reason,
        ]);

        AuditLoggerService::log('Cancel Payment', $payment, $old, [
            'status' => 'cancelled',
            'reason' => $request->cancel_reason,
        ]);

        return back()->with('success', 'تم إلغاء العملية وتوثيق السبب.');
    }

    // ─── Pending Review Queue ──────────────────────────────────────────
    public function pendingQueue(Request $request)
    {
        $payments = Payment::with(['student', 'service'])
            ->where('status', 'pending')
            ->orderByDesc('payment_date')
            ->paginate(20);

        return view('admin.review.pending', compact('payments'));
    }

    public function markPaid(Request $request, Payment $payment)
    {
        if (!auth()->user()->hasPermission('manual_cash_entry')) {
            abort(403, 'غير مصرح لك بتأكيد العمليات يدوياً.');
        }

        if ($payment->status !== 'pending') {
            return back()->withErrors(['error' => 'العملية ليست في حالة انتظار.']);
        }

        $old = ['status' => $payment->status];
        $payment->update([
            'status'           => 'paid',
            'reference_number' => $request->filled('reference_number')
                ? $request->reference_number
                : $payment->reference_number,
        ]);

        AuditLoggerService::log('Manual Mark As Paid', $payment, $old, ['status' => 'paid']);
        return back()->with('success', 'تم تأكيد العملية يدوياً وتحديث حالتها إلى "مكتمل".');
    }

    // ─── Audit Log viewer ─────────────────────────────────────────────
    public function auditLog(Request $request)
    {
        $query = AuditLog::with('user')->orderByDesc('created_at');

        if ($request->filled('action')) {
            $query->where('action', 'like', "%{$request->action}%");
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs      = $query->paginate(30)->withQueryString();
        $employees = User::select('id', 'name')->get();

        return view('admin.audit.index', compact('logs', 'employees'));
    }
}
