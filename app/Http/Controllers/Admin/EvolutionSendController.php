<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppMessage;
use App\Services\WhatsApp\Evolution\EvolutionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EvolutionSendController extends Controller
{
    public function __construct(
        private EvolutionService $evolutionService
    ) {}

    public function textForm(): View
    {
        return view('admin.pages.evolution-api.send.text', [
            'instanceName' => $this->evolutionService->activeInstanceName(),
        ]);
    }

    public function sendText(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'to' => ['required', 'string', 'max:100'],
            'text' => ['required', 'string', 'max:5000'],
        ]);

        $provider = $this->evolutionService->provider();
        $provider->sendText($validated['to'], $validated['text']);

        return back()->with('success', 'تم إرسال الرسالة النصية بنجاح إلى ' . $validated['to'] . '.');
    }

    public function mediaForm(): View
    {
        return view('admin.pages.evolution-api.send.media', [
            'instanceName' => $this->evolutionService->activeInstanceName(),
        ]);
    }

    public function sendMedia(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'to' => ['required', 'string', 'max:100'],
            'mediatype' => ['required', 'in:image,video,audio,document'],
            'media' => ['required', 'string', 'max:2000'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'fileName' => ['nullable', 'string', 'max:255'],
        ]);

        $provider = $this->evolutionService->provider();
        $provider->sendMediaMessage([
            'number' => $validated['to'],
            'mediatype' => $validated['mediatype'],
            'media' => $validated['media'],
            'caption' => $validated['caption'] ?? null,
            'fileName' => $validated['fileName'] ?? null,
        ]);

        return back()->with('success', 'تم إرسال الوسائط (' . $validated['mediatype'] . ') بنجاح.');
    }

    public function advancedForm(string $type): View
    {
        $allowed = ['buttons', 'list', 'poll', 'location', 'contact', 'sticker', 'status'];
        abort_unless(in_array($type, $allowed, true), 404);

        return view('admin.pages.evolution-api.send.advanced', [
            'type' => $type,
            'instanceName' => $this->evolutionService->activeInstanceName(),
        ]);
    }

    public function sendAdvanced(Request $request, string $type): RedirectResponse
    {
        $validated = $request->validate([
            'to' => ['required', 'string', 'max:100'],
            'payload' => ['required', 'string'],
        ]);

        $payload = json_decode($validated['payload'], true);
        if (! is_array($payload)) {
            return back()->with('error', 'صيغة JSON غير صالحة.');
        }

        $payload['number'] = $validated['to'];
        $instance = $this->evolutionService->activeInstanceName();
        $client = $this->evolutionService->client();

        match ($type) {
            'buttons' => $client->sendButtons($instance, $payload),
            'list' => $client->sendList($instance, $payload),
            'poll' => $client->sendPoll($instance, $payload),
            'location' => $client->sendLocation($instance, $payload),
            'contact' => $client->sendContact($instance, $payload),
            'sticker' => $client->sendSticker($instance, $payload),
            'status' => $client->sendStatus($instance, $payload),
            default => abort(404),
        };

        return back()->with('success', 'تم إرسال رسالة ' . $type . ' بنجاح.');
    }

    public function messagesIndex(): View
    {
        $messages = WhatsAppMessage::with('contact')
            ->latest()
            ->paginate(30);

        return view('admin.pages.evolution-api.messages.index', compact('messages'));
    }
}
