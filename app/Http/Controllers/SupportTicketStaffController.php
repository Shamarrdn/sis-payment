<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Notifications\RequestStatusChanged;
use App\Support\TicketCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportTicketStaffController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportTicket::with('student')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $tickets = $query->paginate(15)->withQueryString();
        $categories = TicketCategories::LABELS;

        return view('staff.tickets.index', compact('tickets', 'categories'));
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['student', 'replies.user', 'replies.student']);
        $categories = TicketCategories::LABELS;

        return view('staff.tickets.show', compact('ticket', 'categories'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'status'  => 'nullable|in:open,in_progress,resolved,closed',
        ]);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'message'   => $validated['message'],
        ]);

        if (!empty($validated['status'])) {
            $ticket->update(['status' => $validated['status']]);
        } elseif ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        $student = $ticket->student;
        $student->notify(new RequestStatusChanged([
            'title'        => 'رد على تذكرة الدعم',
            'message'      => 'تم الرد على تذكرتك: ' . $ticket->subject,
            'request_type' => 'support_ticket',
            'request_id'   => $ticket->id,
            'status'       => $ticket->fresh()->status,
            'action_url'   => route('student.tickets.show', $ticket),
        ]));

        return back()->with('success', 'تم إرسال الرد للطالب.');
    }

    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $ticket->update(['status' => $validated['status']]);

        $ticket->student->notify(new RequestStatusChanged([
            'title'        => 'تحديث حالة تذكرة الدعم',
            'message'      => 'تم تحديث حالة تذكرتك إلى: ' . SupportTicket::statusLabel($validated['status']),
            'request_type' => 'support_ticket',
            'request_id'   => $ticket->id,
            'status'       => $validated['status'],
            'action_url'   => route('student.tickets.show', $ticket),
        ]));

        return back()->with('success', 'تم تحديث حالة التذكرة.');
    }
}
