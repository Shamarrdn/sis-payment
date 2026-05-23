<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Faq;
use App\Models\HelpArticle;
use App\Models\Payment;
use App\Models\SensitiveDataRequest;
use App\Models\StudentDocument;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Notifications\NewRequestSubmitted;
use App\Services\StaffNotifier;
use App\Support\TicketCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentCommunicationController extends Controller
{
    public function announcements()
    {
        $student = Auth::guard('student')->user();
        $announcements = Announcement::visibleToStudent($student)
            ->latest()
            ->paginate(10);

        return view('student.communication.announcements', compact('announcements'));
    }

    public function notifications()
    {
        $student = Auth::guard('student')->user();
        $notifications = $student->notifications()->paginate(20);

        return view('student.communication.notifications', compact('notifications'));
    }

    public function markNotificationRead(string $id)
    {
        $student = Auth::guard('student')->user();
        $notification = $student->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        $url = $notification->data['action_url'] ?? route('student.notifications');

        return redirect($url);
    }

    public function markAllNotificationsRead()
    {
        Auth::guard('student')->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'تم تعليم جميع الإشعارات كمقروءة.');
    }

    public function faq()
    {
        $faqs = Faq::where('is_active', true)->orderBy('category')->orderBy('id')->get()->groupBy('category');

        return view('student.communication.faq', compact('faqs'));
    }

    public function helpCenter()
    {
        $articles = HelpArticle::where('is_active', true)->orderBy('category')->orderBy('id')->get()->groupBy('category');

        return view('student.communication.help', compact('articles'));
    }

    public function ticketsIndex()
    {
        $tickets = Auth::guard('student')->user()
            ->tickets()
            ->latest()
            ->paginate(10);

        return view('student.communication.tickets.index', compact('tickets'));
    }

    public function ticketsCreate()
    {
        $categories = TicketCategories::LABELS;

        return view('student.communication.tickets.create', compact('categories'));
    }

    public function ticketsStore(Request $request)
    {
        $validated = $request->validate([
            'subject'  => 'required|string|max:255',
            'category' => 'required|in:' . implode(',', TicketCategories::keys()),
            'message'  => 'required|string|max:5000',
            'priority' => 'nullable|in:low,medium,high',
        ]);

        $student = Auth::guard('student')->user();

        $ticket = SupportTicket::create([
            'student_id' => $student->id,
            'subject'    => $validated['subject'],
            'category'   => $validated['category'],
            'priority'   => $validated['priority'] ?? 'medium',
            'status'     => 'open',
        ]);

        TicketReply::create([
            'ticket_id'  => $ticket->id,
            'student_id' => $student->id,
            'message'    => $validated['message'],
        ]);

        StaffNotifier::notifyRoles(
            ['student_affairs', 'super_admin', 'admin'],
            new NewRequestSubmitted([
                'title'        => 'تذكرة دعم جديدة',
                'message'      => $student->name . ' — ' . $validated['subject'],
                'student_name' => $student->name,
                'request_type' => 'support_ticket',
                'request_id'   => $ticket->id,
                'action_url'   => route('staff.tickets.show', $ticket),
            ])
        );

        return redirect()->route('student.tickets.show', $ticket)
            ->with('success', 'تم فتح تذكرة الدعم بنجاح. سيتم الرد عليك قريباً.');
    }

    public function ticketsShow(SupportTicket $ticket)
    {
        $student = Auth::guard('student')->user();
        if ($ticket->student_id !== $student->id) {
            abort(403);
        }

        $ticket->load(['replies.user', 'replies.student']);

        return view('student.communication.tickets.show', compact('ticket'));
    }

    public function ticketsReply(Request $request, SupportTicket $ticket)
    {
        $student = Auth::guard('student')->user();
        if ($ticket->student_id !== $student->id) {
            abort(403);
        }

        if (in_array($ticket->status, ['closed', 'resolved'])) {
            return back()->withErrors(['message' => 'لا يمكن الرد على تذكرة مغلقة.']);
        }

        $validated = $request->validate(['message' => 'required|string|max:5000']);

        TicketReply::create([
            'ticket_id'  => $ticket->id,
            'student_id' => $student->id,
            'message'    => $validated['message'],
        ]);

        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        StaffNotifier::notifyRoles(
            ['student_affairs', 'super_admin', 'admin'],
            new NewRequestSubmitted([
                'title'        => 'رد جديد على تذكرة دعم',
                'message'      => $student->name . ' — ' . $ticket->subject,
                'student_name' => $student->name,
                'request_type' => 'support_ticket',
                'request_id'   => $ticket->id,
                'action_url'   => route('staff.tickets.show', $ticket),
            ])
        );

        return back()->with('success', 'تم إرسال ردك بنجاح.');
    }

    public function requestTracking()
    {
        $student = Auth::guard('student')->user();

        $payments = Payment::where('student_id', $student->id)
            ->with('service')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($p) => [
                'type'       => 'payment',
                'title'      => $p->service?->name ?? 'دفع',
                'status'     => $p->status,
                'status_label' => match ($p->status) {
                    'paid' => 'مدفوع',
                    'pending' => 'معلق',
                    'failed' => 'فشل',
                    default => $p->status,
                },
                'date'       => $p->created_at,
                'url'        => $p->status === 'paid' ? route('student.receipt', $p) : route('student.history'),
            ]);

        $sensitive = SensitiveDataRequest::where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($r) => [
                'type'         => 'sensitive_data',
                'title'        => 'طلب تعديل بيانات حساسة',
                'status'       => $r->status,
                'status_label' => match ($r->status) {
                    'pending' => 'قيد المراجعة',
                    'approved' => 'معتمد',
                    'rejected' => 'مرفوض',
                    default => $r->status,
                },
                'date'         => $r->created_at,
                'url'          => route('student.profile'),
            ]);

        $documents = StudentDocument::where('student_id', $student->id)
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn ($d) => [
                'type'         => 'document',
                'title'        => 'مستند: ' . match ($d->type) {
                    'national_id' => 'بطاقة الرقم القومي',
                    'birth_certificate' => 'شهادة الميلاد',
                    'personal_photo' => 'صورة شخصية',
                    default => $d->type,
                },
                'status'       => $d->status,
                'status_label' => match ($d->status) {
                    'pending' => 'قيد المراجعة',
                    'verified' => 'معتمد',
                    'rejected' => 'مرفوض',
                    default => $d->status,
                },
                'date'         => $d->updated_at,
                'url'          => route('student.profile'),
            ]);

        $tickets = SupportTicket::where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($t) => [
                'type'         => 'ticket',
                'title'        => $t->subject,
                'status'       => $t->status,
                'status_label' => SupportTicket::statusLabel($t->status),
                'date'         => $t->created_at,
                'url'          => route('student.tickets.show', $t),
            ]);

        $requests = $payments->concat($sensitive)->concat($documents)->concat($tickets)
            ->sortByDesc('date')
            ->values();

        return view('student.communication.requests', compact('requests'));
    }
}
