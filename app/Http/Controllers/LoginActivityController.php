<?php

namespace App\Http\Controllers;

use App\Models\LoginActivity;
use App\Models\User;
use Illuminate\Http\Request;

class LoginActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = LoginActivity::with(['user', 'student'])->latest();

        if ($request->filled('guard')) {
            $query->where('guard', $request->guard);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('success')) {
            $query->where('success', $request->success === '1');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->paginate(30)->withQueryString();
        $employees = User::select('id', 'name')->orderBy('name')->get();

        return view('admin.login-activity', compact('activities', 'employees'));
    }
}
