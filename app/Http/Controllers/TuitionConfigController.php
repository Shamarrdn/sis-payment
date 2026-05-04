<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TuitionConfig;
use App\Models\Faculty;
use App\Models\Department;
use App\Services\AuditLoggerService;

class TuitionConfigController extends Controller
{
    public function index(Request $request)
    {
        $configs     = TuitionConfig::with(['faculty', 'department', 'updatedBy'])
            ->orderByDesc('effective_from')
            ->paginate(25);
        $faculties   = Faculty::where('is_active', true)->with('activeDepartments')->get();
        $categories  = ['Student', 'Graduate', 'Military College', "Master's", 'PhD'];
        $years       = ['الفرقة الأولى', 'الفرقة الثانية', 'الفرقة الثالثة', 'الفرقة الرابعة', 'ماجستير', 'دكتوراه', 'خريج'];

        return view('admin.tuition.index', compact('configs', 'faculties', 'categories', 'years'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'faculty_id'     => 'nullable|exists:faculties,id',
            'department_id'  => 'nullable|exists:departments,id',
            'academic_year'  => 'nullable|string|max:100',
            'user_category'  => 'nullable|string|max:100',
            'tuition_amount' => 'required|numeric|min:0',
            'extra_fee'      => 'nullable|numeric|min:0',
            'effective_from' => 'required|date',
            'effective_to'   => 'nullable|date|after:effective_from',
            'notes'          => 'nullable|string',
        ]);

        $validated['is_active']  = $request->has('is_active');
        $validated['updated_by'] = auth()->id();
        $validated['extra_fee']  = $validated['extra_fee'] ?? 0;

        $config = TuitionConfig::create($validated);
        AuditLoggerService::log('Create Tuition Config', $config, null, $config->toArray());

        return back()->with('success', 'تم إضافة إعداد الرسوم: ' . $config->label());
    }

    public function update(Request $request, TuitionConfig $tuitionConfig)
    {
        $validated = $request->validate([
            'tuition_amount' => 'required|numeric|min:0',
            'extra_fee'      => 'nullable|numeric|min:0',
            'effective_from' => 'required|date',
            'effective_to'   => 'nullable|date',
            'notes'          => 'nullable|string',
        ]);

        $old = $tuitionConfig->toArray();
        $validated['is_active']  = $request->has('is_active');
        $validated['updated_by'] = auth()->id();
        $validated['extra_fee']  = $validated['extra_fee'] ?? 0;

        $tuitionConfig->update($validated);
        AuditLoggerService::log('Update Tuition Config', $tuitionConfig, $old, $tuitionConfig->fresh()->toArray());

        return back()->with('success', 'تم تحديث إعداد الرسوم.');
    }

    public function destroy(TuitionConfig $tuitionConfig)
    {
        AuditLoggerService::log('Delete Tuition Config', $tuitionConfig, $tuitionConfig->toArray());
        $tuitionConfig->delete();
        return back()->with('success', 'تم حذف إعداد الرسوم.');
    }
}
