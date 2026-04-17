<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wapi\PreviewWapiTemplateRequest;
use App\Http\Requests\Wapi\SendWapiCampaignRequest;
use App\Http\Requests\Wapi\SendWapiMessageRequest;
use App\Http\Requests\Wapi\SendWapiTemplateRequest;
use App\Models\WapiTemplate;
use App\Services\WapiOutboundDispatcher;
use App\Support\WapiTemplatePayloadBuilder;
use Illuminate\Http\JsonResponse;

class WhatsAppController extends Controller
{
    public function __construct(
        private WapiOutboundDispatcher $dispatcher
    ) {}

    public function sendMessage(SendWapiMessageRequest $request): JsonResponse
    {
        $path = $request->file('attachment')?->store('wapi-temp', 'local');

        $message = $this->dispatcher->queueMessage(
            (string) $request->input('phone'),
            (string) $request->input('message', ''),
            $path,
            (string) $request->input('header', ''),
            (string) $request->input('footer', ''),
            (string) $request->input('buttons', ''),
        );

        return response()->json([
            'id' => $message->id,
            'status' => $message->status->value,
            'queued' => true,
        ], 202);
    }

    public function sendTemplate(SendWapiTemplateRequest $request): JsonResponse
    {
        $path = $request->file('attachment')?->store('wapi-temp', 'local');

        $headerVars = (array) $request->input('header_variables', []);
        $bodyVars = (array) $request->input('variables', []);

        $message = $this->dispatcher->queueTemplate(
            (string) $request->input('phone'),
            (string) $request->input('template_name'),
            (string) $request->input('language'),
            (array) $request->input('components', []),
            $path,
            $request->input('wapi_template_id') !== null ? (int) $request->input('wapi_template_id') : null,
            [
                'header_variables' => $headerVars,
                'variables' => $bodyVars,
            ],
        );

        return response()->json([
            'id' => $message->id,
            'status' => $message->status->value,
            'queued' => true,
        ], 202);
    }

    public function sendCampaign(SendWapiCampaignRequest $request): JsonResponse
    {
        $headerVars = (array) $request->input('header_variables', []);
        $bodyVars = (array) $request->input('variables', []);

        $message = $this->dispatcher->queueCampaign(
            (string) $request->input('name'),
            (string) $request->input('template_id'),
            (string) $request->input('group_id'),
            $headerVars,
            $bodyVars,
            $request->input('wapi_template_id') !== null ? (int) $request->input('wapi_template_id') : null,
            [
                'header_variables' => $headerVars,
                'variables' => $bodyVars,
            ],
        );

        return response()->json([
            'id' => $message->id,
            'status' => $message->status->value,
            'queued' => true,
        ], 202);
    }

    public function previewTemplate(PreviewWapiTemplateRequest $request): JsonResponse
    {
        $template = WapiTemplate::query()->findOrFail($request->input('wapi_template_id'));
        $headerVars = (array) $request->input('header_variables', []);
        $bodyVars = (array) $request->input('variables', []);

        $flaxxa = WapiTemplatePayloadBuilder::flaxxaComponentsFromVariables($headerVars, $bodyVars);

        $previewText = null;
        $structure = $template->structure;
        if (is_array($structure) && ! empty($structure['preview_template'])) {
            $previewText = WapiTemplatePayloadBuilder::previewBody(
                (string) $structure['preview_template'],
                $bodyVars
            );
        }

        return response()->json([
            'wapi_template_id' => $template->id,
            'template_name' => $template->name,
            'language' => $template->language,
            'flaxxa_payload' => $flaxxa,
            'preview_text' => $previewText,
        ]);
    }
}
