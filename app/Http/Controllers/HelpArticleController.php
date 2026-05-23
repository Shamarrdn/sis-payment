<?php

namespace App\Http\Controllers;

use App\Models\HelpArticle;
use Illuminate\Http\Request;

class HelpArticleController extends Controller
{
    public function index()
    {
        $articles = HelpArticle::orderBy('category')->orderBy('id')->paginate(20);

        return view('admin.content.help', compact('articles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'    => 'required|string|max:255',
            'content'  => 'required|string',
            'category' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);

        HelpArticle::create($validated);

        return back()->with('success', 'تم إضافة المقال بنجاح.');
    }

    public function update(Request $request, HelpArticle $helpArticle)
    {
        $validated = $request->validate([
            'title'    => 'required|string|max:255',
            'content'  => 'required|string',
            'category' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);

        $helpArticle->update($validated);

        return back()->with('success', 'تم تحديث المقال بنجاح.');
    }

    public function destroy(HelpArticle $helpArticle)
    {
        $helpArticle->delete();

        return back()->with('success', 'تم حذف المقال.');
    }
}
