<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faculty;
use App\Models\Department;
use App\Services\AuditLoggerService;

class FacultyController extends Controller
{
    public function index()
    {
        $faculties = Faculty::withCount(['departments', 'students', 'payments'])->get();
        return view('admin.faculties.index', compact('faculties'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:20|unique:faculties,code',
            'description' => 'nullable|string',
        ]);

        $validated['is_active'] = true;
        $faculty = Faculty::create($validated);
        AuditLoggerService::log('Create Faculty', $faculty, null, $faculty->toArray());

        return back()->with('success', 'تم إضافة الكلية بنجاح: ' . $faculty->name);
    }

    public function update(Request $request, Faculty $faculty)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:20|unique:faculties,code,' . $faculty->id,
            'description' => 'nullable|string',
        ]);

        $old = $faculty->toArray();
        $faculty->update($validated);
        AuditLoggerService::log('Update Faculty', $faculty, $old, $faculty->fresh()->toArray());

        return back()->with('success', 'تم تحديث بيانات الكلية.');
    }

    public function toggle(Faculty $faculty)
    {
        $old = ['is_active' => $faculty->is_active];
        $faculty->update(['is_active' => !$faculty->is_active]);
        AuditLoggerService::log('Toggle Faculty', $faculty, $old, ['is_active' => $faculty->is_active]);

        $status = $faculty->is_active ? 'مفعّلة' : 'معطّلة';
        return back()->with('success', "الكلية {$faculty->name} أصبحت {$status}.");
    }

    // ─── Departments (nested resource) ─────────────────────────────────

    public function storeDepartment(Request $request, Faculty $faculty)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20',
        ]);

        $dept = $faculty->departments()->create(array_merge($validated, ['is_active' => true]));
        AuditLoggerService::log('Create Department', $dept, null, $dept->toArray());

        return back()->with('success', 'تم إضافة القسم: ' . $dept->name);
    }

    public function updateDepartment(Request $request, Faculty $faculty, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20',
        ]);

        $old = $department->toArray();
        $department->update($validated);
        AuditLoggerService::log('Update Department', $department, $old, $department->fresh()->toArray());

        return back()->with('success', 'تم تحديث القسم.');
    }

    public function toggleDepartment(Faculty $faculty, Department $department)
    {
        $old = ['is_active' => $department->is_active];
        $department->update(['is_active' => !$department->is_active]);
        AuditLoggerService::log('Toggle Department', $department, $old, ['is_active' => $department->is_active]);

        $status = $department->is_active ? 'مفعّلاً' : 'معطّلاً';
        return back()->with('success', "القسم {$department->name} أصبح {$status}.");
    }
}
