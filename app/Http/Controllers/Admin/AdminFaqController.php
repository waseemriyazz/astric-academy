<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminFaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::orderBy('sort_order')->paginate(20);
        return view('admin.faqs.index', compact('faqs'));
    }

    public function create()
    {
        return view('admin.faqs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        Faq::create($request->only('question', 'answer', 'sort_order'));

        Log::info('Admin created FAQ', [
            'admin_id' => auth()->id(),
            'question' => $request->question,
        ]);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ created successfully.');
    }

    public function edit(Faq $faq)
    {
        return view('admin.faqs.edit', compact('faq'));
    }

    public function update(Request $request, Faq $faq)
    {
        $request->validate([
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $faq->update($request->only('question', 'answer', 'sort_order'));

        Log::info('Admin updated FAQ', [
            'admin_id' => auth()->id(),
            'faq_id' => $faq->id,
        ]);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ updated successfully.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        Log::info('Admin deleted FAQ', [
            'admin_id' => auth()->id(),
            'faq_id' => $faq->id,
        ]);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ deleted successfully.');
    }
}