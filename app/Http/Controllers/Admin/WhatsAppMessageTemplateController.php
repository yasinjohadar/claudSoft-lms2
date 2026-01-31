<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppMessageTemplate;
use Illuminate\Http\Request;

class WhatsAppMessageTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = WhatsAppMessageTemplate::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active == '1');
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        $templates = $query->orderBy('name')->paginate(15);

        return view('admin.pages.whatsapp-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.pages.whatsapp-templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:100|unique:whatsapp_message_templates,slug',
            'body' => 'required|string',
            'type' => 'required|in:text,template',
            'language' => 'required|string|max:10',
            'meta_template_name' => 'nullable|string|max:255',
            'variables' => 'nullable|array',
            'variables.*' => 'string|max:50',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        if (!empty($validated['variables'])) {
            $validated['variables'] = array_values(array_filter($validated['variables']));
        } else {
            $validated['variables'] = null;
        }
        if ($validated['type'] === 'template' && empty($validated['meta_template_name'])) {
            $validated['meta_template_name'] = null;
        }

        WhatsAppMessageTemplate::create($validated);

        return redirect()->route('admin.whatsapp-templates.index')
            ->with('success', 'تم إنشاء قالب الرسالة بنجاح.');
    }

    public function edit(WhatsAppMessageTemplate $whatsapp_template)
    {
        return view('admin.pages.whatsapp-templates.edit', compact('whatsapp_template'));
    }

    public function update(Request $request, WhatsAppMessageTemplate $whatsapp_template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:100|unique:whatsapp_message_templates,slug,' . $whatsapp_template->id,
            'body' => 'required|string',
            'type' => 'required|in:text,template',
            'language' => 'required|string|max:10',
            'meta_template_name' => 'nullable|string|max:255',
            'variables' => 'nullable|array',
            'variables.*' => 'string|max:50',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        if (!empty($validated['variables'])) {
            $validated['variables'] = array_values(array_filter($validated['variables']));
        } else {
            $validated['variables'] = null;
        }
        if ($validated['type'] === 'template' && empty($validated['meta_template_name'])) {
            $validated['meta_template_name'] = null;
        }

        $whatsapp_template->update($validated);

        return redirect()->route('admin.whatsapp-templates.index')
            ->with('success', 'تم تحديث قالب الرسالة بنجاح.');
    }

    public function destroy(WhatsAppMessageTemplate $whatsapp_template)
    {
        $whatsapp_template->delete();
        return redirect()->route('admin.whatsapp-templates.index')
            ->with('success', 'تم حذف قالب الرسالة بنجاح.');
    }

    /**
     * API: get template by id (for use in send form / other places).
     */
    public function getTemplate(WhatsAppMessageTemplate $whatsapp_template)
    {
        return response()->json([
            'id' => $whatsapp_template->id,
            'name' => $whatsapp_template->name,
            'body' => $whatsapp_template->body,
            'type' => $whatsapp_template->type,
            'language' => $whatsapp_template->language,
            'meta_template_name' => $whatsapp_template->meta_template_name,
            'variables' => $whatsapp_template->variables ?? [],
        ]);
    }
}
