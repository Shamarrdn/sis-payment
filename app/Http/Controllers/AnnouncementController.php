<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Faculty;
use App\Models\Student;
use App\Notifications\AnnouncementPublished;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with(['faculty', 'department', 'creator'])->latest()->paginate(15);
        return view('admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        $faculties = Faculty::where('is_active', true)->with('activeDepartments')->get();
        return view('admin.announcements.create', compact('faculties'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'faculty_id' => 'nullable|exists:faculties,id',
            'department_id' => 'nullable|exists:departments,id',
            'academic_year' => 'nullable|string|max:100',
            'expires_at' => 'nullable|date|after:now',
            'is_published' => 'required|boolean',
        ]);

        if (!empty($validated['department_id']) && !empty($validated['faculty_id'])) {
            $department = \App\Models\Department::find($validated['department_id']);
            if ($department && $department->faculty_id !== (int)$validated['faculty_id']) {
                return back()->withErrors(['department_id' => 'القسم المحدد لا يتبع الكلية المحددة.'])->withInput();
            }
        }

        $validated['created_by'] = auth()->id();

        $announcement = Announcement::create($validated);

        // Notify matching students if published
        if ($announcement->is_published) {
            $studentsQuery = Student::query();
            if ($announcement->faculty_id) {
                $studentsQuery->where('faculty_id', $announcement->faculty_id);
            }
            if ($announcement->department_id) {
                $studentsQuery->where('department_id', $announcement->department_id);
            }
            if ($announcement->academic_year) {
                $studentsQuery->where('academic_year', $announcement->academic_year);
            }

            $students = $studentsQuery->get();
            foreach ($students as $student) {
                $student->notify(new AnnouncementPublished([
                    'title' => 'إعلان جديد: ' . $announcement->title,
                    'message' => Str::limit(strip_tags($announcement->content), 100),
                    'announcement_id' => $announcement->id,
                    'action_url' => route('student.announcements'),
                ]));
            }
        }

        return redirect()->route('admin.announcements.index')->with('success', 'تم نشر الإعلان وإخطار الطلاب بنجاح.');
    }

    public function edit(Announcement $announcement)
    {
        $faculties = Faculty::where('is_active', true)->with('activeDepartments')->get();
        return view('admin.announcements.edit', compact('announcement', 'faculties'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'faculty_id' => 'nullable|exists:faculties,id',
            'department_id' => 'nullable|exists:departments,id',
            'academic_year' => 'nullable|string|max:100',
            'expires_at' => 'nullable|date|after:now',
            'is_published' => 'required|boolean',
        ]);

        if (!empty($validated['department_id']) && !empty($validated['faculty_id'])) {
            $department = \App\Models\Department::find($validated['department_id']);
            if ($department && $department->faculty_id !== (int)$validated['faculty_id']) {
                return back()->withErrors(['department_id' => 'القسم المحدد لا يتبع الكلية المحددة.'])->withInput();
            }
        }

        $announcement->update($validated);

        return redirect()->route('admin.announcements.index')->with('success', 'تم تحديث الإعلان بنجاح.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return redirect()->route('admin.announcements.index')->with('success', 'تم حذف الإعلان بنجاح.');
    }
}
