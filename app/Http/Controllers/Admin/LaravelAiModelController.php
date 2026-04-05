<?php

namespace App\Http\Controllers\Admin;

use App\Ai\Agents\PingAgent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLaravelAiModelRequest;
use App\Http\Requests\Admin\TestLaravelAiTempRequest;
use App\Http\Requests\Admin\UpdateLaravelAiModelRequest;
use App\Models\LaravelAiModel;
use App\Services\AiNew\LaravelAiPromptRunner;
use App\Services\AiNew\LaravelAiProviderManager;
use App\Services\AiNew\LaravelAiRequestLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class LaravelAiModelController extends Controller
{
    public function __construct(
        private LaravelAiProviderManager $providerManager,
        private LaravelAiRequestLogger $logger,
        private LaravelAiPromptRunner $promptRunner,
    ) {}

    public function index(Request $request): View
    {
        $activeFilter = $request->query('active');
        if ($activeFilter === null || $activeFilter === '') {
            $active = null;
        } else {
            $active = filter_var($activeFilter, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        $models = LaravelAiModel::query()
            ->with('creator')
            ->filterActive($active)
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $providers = LaravelAiModel::providerLabels();
        $capabilities = LaravelAiModel::capabilityLabels();

        return view('admin.laravel-ai.models.index', compact('models', 'providers', 'capabilities', 'activeFilter'));
    }

    public function create(): View
    {
        $providers = LaravelAiModel::providerLabels();
        $capabilities = LaravelAiModel::capabilityLabels();

        return view('admin.laravel-ai.models.create', compact('providers', 'capabilities'));
    }

    public function store(StoreLaravelAiModelRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['created_by'] = Auth::id();
        $caps = $request->input('capabilities');
        $data['capabilities'] = is_array($caps) && count($caps) > 0 ? array_values($caps) : null;

        LaravelAiModel::create($data);

        return redirect()
            ->route('admin.ai-sdk.models.index')
            ->with('success', 'تم إنشاء موديل Laravel AI بنجاح.');
    }

    public function edit(LaravelAiModel $laravel_ai_model): View
    {
        $providers = LaravelAiModel::providerLabels();
        $capabilities = LaravelAiModel::capabilityLabels();

        return view('admin.laravel-ai.models.edit', [
            'model' => $laravel_ai_model,
            'providers' => $providers,
            'capabilities' => $capabilities,
        ]);
    }

    public function update(UpdateLaravelAiModelRequest $request, LaravelAiModel $laravel_ai_model): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $caps = $request->input('capabilities');
        $data['capabilities'] = is_array($caps) && count($caps) > 0 ? array_values($caps) : null;

        $apiKey = isset($data['api_key']) ? trim((string) $data['api_key']) : '';
        unset($data['api_key']);

        if ($apiKey !== '') {
            $laravel_ai_model->api_key = $apiKey;
        }

        $laravel_ai_model->fill($data);
        $laravel_ai_model->save();

        return redirect()
            ->route('admin.ai-sdk.models.index')
            ->with('success', 'تم تحديث موديل Laravel AI بنجاح.');
    }

    public function destroy(LaravelAiModel $laravel_ai_model): RedirectResponse
    {
        $laravel_ai_model->delete();

        return redirect()
            ->route('admin.ai-sdk.models.index')
            ->with('success', 'تم حذف الموديل.');
    }

    public function testTemp(TestLaravelAiTempRequest $request): JsonResponse
    {
        $started = hrtime(true);
        $data = $request->validated();

        $temp = new LaravelAiModel([
            'provider' => $data['provider'],
            'model' => $data['model'],
            'base_url' => $data['base_url'] ?? null,
        ]);
        $temp->setRawApiKeyForTesting($data['api_key']);

        try {
            $this->executeConnectionPing($temp, 45);
            $latencyMs = (int) ((hrtime(true) - $started) / 1_000_000);

            return $this->testResultJson(true, 'الاتصال ناجح.', $latencyMs);
        } catch (Throwable $e) {
            $latencyMs = (int) ((hrtime(true) - $started) / 1_000_000);
            Log::warning('Laravel AI test-temp failed', ['exception' => $e->getMessage()]);

            return $this->testResultJson(
                false,
                'فشل الاختبار: '.$this->friendlyTestExceptionMessage($e),
                $latencyMs,
                500,
            );
        }
    }

    public function test(LaravelAiModel $laravel_ai_model): JsonResponse
    {
        $started = hrtime(true);
        $operation = 'connection.test';

        try {
            $this->executeConnectionPing($laravel_ai_model, 45);

            $latencyMs = (int) ((hrtime(true) - $started) / 1_000_000);

            $this->logger->logSuccess(
                $laravel_ai_model,
                Auth::user(),
                $operation,
                ['prompt' => 'Reply with OK only.'],
                ['message' => 'OK'],
                $latencyMs,
            );

            return $this->testResultJson(true, 'الاتصال ناجح.', $latencyMs);
        } catch (Throwable $e) {
            $latencyMs = (int) ((hrtime(true) - $started) / 1_000_000);
            Log::error('Laravel AI model test failed', [
                'model_id' => $laravel_ai_model->id,
                'exception' => $e->getMessage(),
            ]);

            $this->logger->logFailure(
                $laravel_ai_model,
                Auth::user(),
                $operation,
                ['prompt' => 'Reply with OK only.'],
                $e->getMessage(),
                $latencyMs,
            );

            return $this->testResultJson(
                false,
                'فشل الاختبار: '.$this->friendlyTestExceptionMessage($e),
                $latencyMs,
                500,
            );
        }
    }

    private function executeConnectionPing(LaravelAiModel $model, int $timeout = 45): void
    {
        $this->providerManager->runWithModel($model, function () use ($model, $timeout) {
            $this->promptRunner->runPlain(
                $model,
                new PingAgent,
                'Reply with OK only.',
                $timeout,
            );
        });
    }

    private function testResultJson(bool $success, string $message, int $latencyMs, int $http = 200): JsonResponse
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'latency_ms' => $latencyMs,
            'response_time_ms' => $latencyMs,
        ], $http);
    }

    private function friendlyTestExceptionMessage(Throwable $e): string
    {
        $msg = $e->getMessage();
        if (
            str_contains($msg, 'Promptable')
            || str_contains($msg, 'Laravel\\Ai\\')
            || (str_contains($msg, 'Class ') && str_contains($msg, 'not found'))
        ) {
            return $msg.' — تأكد من تثبيت حزمة laravel/ai وتشغيل composer install في جذر المشروع.';
        }

        if (
            str_contains($msg, 'cURL error 60')
            || str_contains($msg, 'SSL certificate problem')
            || str_contains($msg, 'unable to get local issuer certificate')
        ) {
            return $msg.' — حمّل https://curl.se/ca/cacert.pem واحفظه كـ storage/cacert.pem في المشروع (يُكتشف تلقائياً)، أو عيّن AI_HTTP_VERIFY=المسار_الكامل في .env، أو curl.cainfo في php.ini. لا تستخدم false في الإنتاج.';
        }

        return $msg;
    }
}
