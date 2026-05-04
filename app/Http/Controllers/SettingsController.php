<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemSetting;
use App\Models\Discount;
use App\Services\AuditLoggerService;

class SettingsController extends Controller
{
    // ─── Financial Settings Panel ──────────────────────────────────────
    public function index()
    {
        $settings  = SystemSetting::orderBy('group')->orderBy('label')->get()->groupBy('group');
        $discounts = Discount::orderBy('category')->get();
        return view('admin.settings.index', compact('settings', 'discounts'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings'         => 'required|array',
            'settings.*.value' => 'nullable|string',
        ]);

        $old = SystemSetting::pluck('value', 'key')->toArray();

        foreach ($request->settings as $key => $data) {
            $setting = SystemSetting::where('key', $key)->first();
            if ($setting) {
                $setting->update(['value' => $data['value'] ?? null]);
            }
        }

        $new = SystemSetting::pluck('value', 'key')->toArray();
        AuditLoggerService::log('Update System Settings', null, $old, $new);

        return back()->with('success', 'تم حفظ الإعدادات المالية بنجاح.');
    }

    // ─── Discount / Scholarship Management ────────────────────────────
    public function storeDiscount(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'category'            => 'required|string|max:255',
            'type'                => 'required|in:full,partial,percentage',
            'value'               => 'required|numeric|min:0',
            'approving_authority' => 'nullable|string|max:255',
            'notes'               => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $discount = Discount::create($validated);

        AuditLoggerService::log('Create Discount', $discount, null, $discount->toArray());
        return back()->with('success', 'تم إضافة الإعفاء/الخصم بنجاح.');
    }

    public function updateDiscount(Request $request, Discount $discount)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'category'            => 'required|string|max:255',
            'type'                => 'required|in:full,partial,percentage',
            'value'               => 'required|numeric|min:0',
            'approving_authority' => 'nullable|string|max:255',
            'notes'               => 'nullable|string',
        ]);

        $old = $discount->toArray();
        $validated['is_active'] = $request->has('is_active');
        $discount->update($validated);

        AuditLoggerService::log('Update Discount', $discount, $old, $discount->fresh()->toArray());
        return back()->with('success', 'تم تحديث الإعفاء/الخصم بنجاح.');
    }

    public function deleteDiscount(Discount $discount)
    {
        AuditLoggerService::log('Delete Discount', $discount, $discount->toArray());
        $discount->delete();
        return back()->with('success', 'تم حذف الإعفاء.');
    }

    // ─── Employee Permission Management ────────────────────────────────
    public function updatePermissions(Request $request, \App\Models\User $user)
    {
        $old = ['permissions' => $user->permissions];
        $permissions = array_keys(array_filter($request->except('_token', '_method')));
        $user->update(['permissions' => $permissions]);

        AuditLoggerService::log('Update Employee Permissions', $user, $old, ['permissions' => $permissions]);
        return back()->with('success', 'تم تحديث صلاحيات الموظف.');
    }
}
