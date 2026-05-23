<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Service;
use App\Models\Student;
use App\Services\PaymentRequestService;
use App\Services\ScopeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function todaySummary()
    {
        $user = auth()->user();
        $today = Carbon::today();
        $payQuery = ScopeService::scopePayments(Payment::query(), $user);

        $newToday = (clone $payQuery)->whereDate('created_at', $today)
            ->where('status', 'paid')
            ->where('fulfillment_status', 'awaiting_processing')
            ->count();

        $completedToday = (clone $payQuery)->whereDate('completed_at', $today)
            ->where('fulfillment_status', 'completed')
            ->count();

        $allOpen = (clone $payQuery)->with('service')
            ->whereIn('fulfillment_status', ['awaiting_processing', 'in_progress'])
            ->where('status', 'paid')
            ->get();

        $delayed = $allOpen->filter(fn ($p) => PaymentRequestService::isDelayed($p));

        return view('reports.today', compact('newToday', 'completedToday', 'delayed', 'allOpen'));
    }

    public function monthlySummary(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $user = auth()->user();
        $payQuery = ScopeService::scopePayments(Payment::query(), $user);

        $stats = [
            'paid_count'    => (clone $payQuery)->where('status', 'paid')->whereBetween('payment_date', [$start, $end])->count(),
            'revenue'       => (clone $payQuery)->where('status', 'paid')->whereBetween('payment_date', [$start, $end])->sum('total_amount'),
            'students_paid' => (clone $payQuery)->where('status', 'paid')->whereBetween('payment_date', [$start, $end])->distinct('student_id')->count('student_id'),
            'completed'     => (clone $payQuery)->where('fulfillment_status', 'completed')->whereBetween('completed_at', [$start, $end])->count(),
        ];

        $stuQuery = ScopeService::scopeStudents(Student::query(), $user);
        $stats['total_students'] = (clone $stuQuery)->count();

        $topServices = (clone $payQuery)->where('status', 'paid')
            ->whereBetween('payment_date', [$start, $end])
            ->select('service_id', DB::raw('count(*) as total'))
            ->groupBy('service_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->load('service');

        return view('reports.monthly', compact('stats', 'topServices', 'month'));
    }

    public function delayed()
    {
        $user = auth()->user();
        $payments = ScopeService::scopePayments(
            Payment::with(['student', 'service'])
                ->where('status', 'paid')
                ->whereIn('fulfillment_status', ['awaiting_processing', 'in_progress']),
            $user
        )->orderBy('payment_date')->get()->filter(fn ($p) => PaymentRequestService::isDelayed($p));

        return view('reports.delayed', compact('payments'));
    }

    public function popularServices(Request $request)
    {
        $user = auth()->user();
        $from = $request->input('date_from', now()->subMonths(3)->format('Y-m-d'));
        $to = $request->input('date_to', now()->format('Y-m-d'));

        $rows = ScopeService::scopePayments(Payment::query(), $user)
            ->where('status', 'paid')
            ->whereDate('payment_date', '>=', $from)
            ->whereDate('payment_date', '<=', $to)
            ->select('service_id', DB::raw('count(*) as requests'), DB::raw('sum(total_amount) as revenue'))
            ->groupBy('service_id')
            ->orderByDesc('requests')
            ->get()
            ->load('service');

        return view('reports.popular-services', compact('rows', 'from', 'to'));
    }

    public function recentActivities()
    {
        $logs = \App\Models\AuditLog::with('user')->latest()->limit(25)->get();
        $logins = \App\Models\LoginActivity::with(['user', 'student'])->latest()->limit(15)->get();

        return view('reports.recent-activities', compact('logs', 'logins'));
    }
}
