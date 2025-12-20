<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebhookToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebhookTokenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tokens = WebhookToken::latest()->paginate(15);
        return view('admin.webhooks.tokens.index', compact('tokens'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sources = WebhookToken::getSources();
        return view('admin.webhooks.tokens.create', compact('sources'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'source' => 'required|in:wpforms,n8n,other',
            'token' => 'required|string|min:10',
            'allowed_ips' => 'nullable|string',
            'form_types' => 'nullable|string',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        try {
            $data = $request->all();

            // Parse allowed_ips (comma-separated string to array)
            if ($request->filled('allowed_ips')) {
                $ips = array_filter(array_map('trim', explode(',', $request->allowed_ips)));
                $data['allowed_ips'] = !empty($ips) ? $ips : null;
            } else {
                $data['allowed_ips'] = null;
            }

            // Parse form_types (JSON string or comma-separated)
            if ($request->filled('form_types')) {
                $formTypes = $request->form_types;
                // Try to decode as JSON first
                $decoded = json_decode($formTypes, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data['form_types'] = $decoded;
                } else {
                    // If not JSON, treat as key:value pairs
                    $pairs = [];
                    foreach (explode(',', $formTypes) as $pair) {
                        $parts = explode(':', trim($pair));
                        if (count($parts) === 2) {
                            $pairs[trim($parts[0])] = trim($parts[1]);
                        }
                    }
                    $data['form_types'] = !empty($pairs) ? $pairs : null;
                }
            } else {
                $data['form_types'] = null;
            }

            $data['is_active'] = $request->has('is_active');

            WebhookToken::create($data);

            return redirect()->route('admin.webhooks.tokens.index')
                ->with('success', 'تم إضافة التوكن بنجاح!');
        } catch (\Exception $e) {
            Log::error('Error creating webhook token: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء إضافة التوكن: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(WebhookToken $token)
    {
        return view('admin.webhooks.tokens.show', compact('token'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WebhookToken $token)
    {
        $sources = WebhookToken::getSources();
        return view('admin.webhooks.tokens.edit', compact('token', 'sources'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WebhookToken $token)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'source' => 'required|in:wpforms,n8n,other',
            'token' => 'nullable|string|min:10',
            'allowed_ips' => 'nullable|string',
            'form_types' => 'nullable|string',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        try {
            $data = $request->except(['token']);

            // Only update token if provided
            if ($request->filled('token')) {
                $data['token'] = $request->token;
            }

            // Parse allowed_ips
            if ($request->filled('allowed_ips')) {
                $ips = array_filter(array_map('trim', explode(',', $request->allowed_ips)));
                $data['allowed_ips'] = !empty($ips) ? $ips : null;
            } else {
                $data['allowed_ips'] = null;
            }

            // Parse form_types
            if ($request->filled('form_types')) {
                $formTypes = $request->form_types;
                $decoded = json_decode($formTypes, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data['form_types'] = $decoded;
                } else {
                    $pairs = [];
                    foreach (explode(',', $formTypes) as $pair) {
                        $parts = explode(':', trim($pair));
                        if (count($parts) === 2) {
                            $pairs[trim($parts[0])] = trim($parts[1]);
                        }
                    }
                    $data['form_types'] = !empty($pairs) ? $pairs : null;
                }
            } else {
                $data['form_types'] = null;
            }

            $data['is_active'] = $request->has('is_active');

            $token->update($data);

            return redirect()->route('admin.webhooks.tokens.index')
                ->with('success', 'تم تحديث التوكن بنجاح!');
        } catch (\Exception $e) {
            Log::error('Error updating webhook token: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحديث التوكن: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WebhookToken $token)
    {
        try {
            $token->delete();
            return redirect()->route('admin.webhooks.tokens.index')
                ->with('success', 'تم حذف التوكن بنجاح!');
        } catch (\Exception $e) {
            Log::error('Error deleting webhook token: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء حذف التوكن: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active status
     */
    public function toggleActive(WebhookToken $token)
    {
        $token->is_active = !$token->is_active;
        $token->save();

        return response()->json([
            'success' => true,
            'is_active' => $token->is_active,
            'message' => $token->is_active ? 'تم تفعيل التوكن' : 'تم إلغاء تفعيل التوكن',
        ]);
    }

    /**
     * Generate a random token
     */
    public function generateToken()
    {
        $token = Str::random(64);
        return response()->json(['token' => $token]);
    }
}
