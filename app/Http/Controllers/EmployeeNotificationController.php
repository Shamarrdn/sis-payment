<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class EmployeeNotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(20);

        return view('staff.notifications', compact('notifications'));
    }

    public function read(string $id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return redirect($notification->data['action_url'] ?? route('staff.notifications'));
    }

    public function readAll()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'تم تعليم جميع الإشعارات كمقروءة.');
    }
}
