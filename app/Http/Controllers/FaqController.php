<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::orderBy('category')->orderBy('id')->paginate(20);

        return view('admin.content.faqs', compact('faqs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'answer'   => 'required|string',
            'category' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);

        Faq::create($validated);

        return back()->with('success', 'تم إضافة السؤال بنجاح.');
    }

    public function update(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'answer'   => 'required|string',
            'category' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);

        $faq->update($validated);

        return back()->with('success', 'تم تحديث السؤال بنجاح.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return back()->with('success', 'تم حذف السؤال.');
    }
}
