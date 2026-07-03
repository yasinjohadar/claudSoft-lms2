<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TelegramMessageTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TelegramMessageTemplateController extends Controller
{
    public function index(): View
    {
        $templates = TelegramMessageTemplate::orderBy('name')->paginate(20);

        return view('admin.pages.telegram.templates.index', compact('templates'));
    }

    public function create(): View
    {
        return view('admin.pages.telegram.templates.form', ['template' => new TelegramMessageTemplate]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTemplate($request);
        $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(4);
        $validated['is_active'] = $request->boolean('is_active');

        TelegramMessageTemplate::create($validated);

        return redirect()->route('admin.telegram.templates.index')->with('success', 'تم إنشاء القالب.');
    }

    public function edit(TelegramMessageTemplate $telegram_template): View
    {
        return view('admin.pages.telegram.templates.form', ['template' => $telegram_template]);
    }

    public function update(Request $request, TelegramMessageTemplate $telegram_template): RedirectResponse
    {
        $validated = $this->validateTemplate($request);
        $validated['is_active'] = $request->boolean('is_active');
        $telegram_template->update($validated);

        return redirect()->route('admin.telegram.templates.index')->with('success', 'تم تحديث القالب.');
    }

    public function destroy(TelegramMessageTemplate $telegram_template): RedirectResponse
    {
        $telegram_template->delete();

        return back()->with('success', 'تم حذف القالب.');
    }

    private function validateTemplate(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'body' => 'required|string|max:10000',
        ]);
    }
}
