<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faculty;
use App\Models\Payment;
use App\Models\Student;
use App\Services\ScopeService;

class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        $user      = auth()->user();
        $faculties = Faculty::withCount(['students', 'payments'])
            ->with(['departments' => function ($q) { $q->withCount('students'); }])
            ->where('is_active', true)
            ->get();

        // Global totals (scoped for non-super-admins)
        $payQuery = ScopeService::scopePayments(Payment::query(), $user);
        $stuQuery = ScopeService::scopeStudents(Student::query(), $user);

        $global = [
            'total_revenue'    => (clone $payQuery)->where('status', 'paid')->sum('total_amount'),
            'total_payments'   => (clone $payQuery)->where('status', 'paid')->count(),
            'pending_payments' => (clone $payQuery)->where('status', 'pending')->count(),
            'failed_payments'  => (clone $payQuery)->where('status', 'failed')->count(),
            'total_students'   => (clone $stuQuery)->count(),
            'refund_requests'  => (clone $payQuery)->where('refund_status', 'requested')->count(),
        ];

        // Per-faculty breakdown
        $byFaculty = $faculties->map(function ($faculty) {
            $tuitionRevenue = Payment::where('faculty_id', $faculty->id)
                ->where('status', 'paid')
                ->whereHas('service', fn($q) => $q->where('type', 'LIKE', '%مصاريف%'))
                ->sum('total_amount');

            $otherRevenue = Payment::where('faculty_id', $faculty->id)
                ->where('status', 'paid')
                ->whereHas('service', fn($q) => $q->where('type', 'NOT LIKE', '%مصاريف%'))
                ->sum('total_amount');

            return [
                'faculty'         => $faculty,
                'revenue'         => Payment::where('faculty_id', $faculty->id)->where('status', 'paid')->sum('total_amount'),
                'payments'        => Payment::where('faculty_id', $faculty->id)->where('status', 'paid')->count(),
                'pending'         => Payment::where('faculty_id', $faculty->id)->where('status', 'pending')->count(),
                'students'        => Student::where('faculty_id', $faculty->id)->count(),
                'tuition_revenue' => $tuitionRevenue,
                'other_revenue'   => $otherRevenue,
                'top_service'     => Payment::where('payments.faculty_id', $faculty->id)
                    ->join('services', 'payments.service_id', '=', 'services.id')
                    ->selectRaw('services.name, count(*) as cnt')
                    ->groupBy('services.name')
                    ->orderByDesc('cnt')
                    ->value('services.name'),
            ];
        });

        // Payments without faculty assignment
        $global['unassigned_payments'] = Payment::whereNull('faculty_id')->where('status', 'paid')->count();
        $global['unassigned_revenue']  = Payment::whereNull('faculty_id')->where('status', 'paid')->sum('total_amount');


        return view('admin.stats.index', compact('global', 'byFaculty', 'faculties'));
    }

    public function byFaculty(Faculty $faculty)
    {
        $departments = $faculty->departments()->withCount('students')->get();

        $stats = [
            'revenue'    => Payment::where('faculty_id', $faculty->id)->where('status', 'paid')->sum('total_amount'),
            'payments'   => Payment::where('faculty_id', $faculty->id)->where('status', 'paid')->count(),
            'pending'    => Payment::where('faculty_id', $faculty->id)->where('status', 'pending')->count(),
            'students'   => Student::where('faculty_id', $faculty->id)->count(),
            'refunds'    => Payment::where('faculty_id', $faculty->id)->where('refund_status', 'requested')->count(),
        ];

        // By department
        $byDept = $departments->map(function ($dept) use ($faculty) {
            return [
                'dept'     => $dept,
                'revenue'  => Payment::where('department_id', $dept->id)->where('status', 'paid')->sum('total_amount'),
                'payments' => Payment::where('department_id', $dept->id)->where('status', 'paid')->count(),
                'students' => $dept->students_count,
            ];
        });

        // By academic year
        $byYear = Payment::where('faculty_id', $faculty->id)
            ->where('status', 'paid')
            ->selectRaw('academic_year, count(*) as cnt, sum(total_amount) as revenue')
            ->groupBy('academic_year')
            ->orderByDesc('revenue')
            ->get();

        return view('admin.stats.faculty', compact('faculty', 'stats', 'byDept', 'byYear', 'departments'));
    }
}
