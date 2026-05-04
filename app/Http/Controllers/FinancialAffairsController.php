<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Payment;
use App\Models\Service;
use App\Services\ScopeService;
use Carbon\Carbon;

class FinancialAffairsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();
        
        $payQuery = ScopeService::scopePayments(Payment::query(), $user);
        
        $todayTotal = (clone $payQuery)->whereDate('payment_date', $today)->where('status', 'paid')->sum('total_amount');
        $todayCount = (clone $payQuery)->whereDate('payment_date', $today)->where('status', 'paid')->count();
        $monthTotal = (clone $payQuery)->whereMonth('payment_date', now()->month)->whereYear('payment_date', now()->year)->where('status', 'paid')->sum('total_amount');
        
        $todayPayments = (clone $payQuery)->with(['student', 'service'])->whereDate('payment_date', $today)->latest('payment_date')->get();

        return view('affairs.financial.index', compact('todayTotal', 'todayCount', 'monthTotal', 'todayPayments'));
    }

    public function payments(Request $request)
    {
        $user = auth()->user();
        $query = Payment::with(['student', 'service'])->latest('payment_date');
        $query = ScopeService::scopePayments($query, $user);

        if ($request->filled('start_date')) {
            $query->whereDate('payment_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }
        if ($request->filled('service_type')) {
            $query->whereHas('service', function ($q) use ($request) {
                $q->where('type', $request->service_type);
            });
        }
        if ($request->filled('faculty_id')) {
            $query->where('faculty_id', $request->faculty_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', fn($q) => $q->where('name', 'like', "%{$search}%")
                                                     ->orWhere('national_id', 'like', "%{$search}%"));
        }

        $payments     = $query->paginate(20);
        $serviceTypes = Service::select('type')->distinct()->pluck('type');
        $faculties    = \App\Models\Faculty::where('is_active', true)->get();

        return view('affairs.financial.payments', compact('payments', 'serviceTypes', 'faculties'));
    }
}
