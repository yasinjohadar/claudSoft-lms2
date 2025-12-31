<?php

namespace App\Services\AI;

use App\Models\AIModel;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AIModelService
{
    /**
     * إنشاء موديل جديد
     */
    public function createModel(array $data, ?User $user = null): AIModel
    {
        if ($user) {
            $data['created_by'] = $user->id;
        }

        // إذا كان هذا الموديل هو الافتراضي، إلغاء الافتراضي من الموديلات الأخرى
        if (isset($data['is_default']) && $data['is_default']) {
            AIModel::where('is_default', true)->update(['is_default' => false]);
        }

        return AIModel::create($data);
    }

    /**
     * تحديث موديل
     */
    public function updateModel(AIModel $model, array $data): AIModel
    {
        // إذا كان هذا الموديل هو الافتراضي، إلغاء الافتراضي من الموديلات الأخرى
        if (isset($data['is_default']) && $data['is_default'] && !$model->is_default) {
            AIModel::where('is_default', true)->where('id', '!=', $model->id)->update(['is_default' => false]);
        }

        // إذا كان هناك api_key جديد، استخدم mutator لتشفيره
        $hasApiKey = isset($data['api_key']) && !empty(trim($data['api_key']));
        if ($hasApiKey) {
            $apiKeyValue = trim($data['api_key']);
            $model->api_key = $apiKeyValue; // Mutator سيقوم بتشفيره تلقائياً
            Log::info('API Key updated for model', [
                'model_id' => $model->id,
                'key_length' => strlen($apiKeyValue)
            ]);
        }
        
        // إزالة api_key من البيانات قبل update
        unset($data['api_key']);

        // تحديث البيانات الأخرى
        if (!empty($data)) {
            $model->update($data);
        }
        
        // إذا تم تحديث api_key، احفظه بشكل منفصل (لأن update قد لا يستدعي mutator)
        if ($hasApiKey) {
            $model->save(); // تأكد من الحفظ
            Log::info('Model saved with API Key', ['model_id' => $model->id]);
        }
        
        // تحديث الـ model من قاعدة البيانات
        $model->refresh();
        
        // التحقق من أن API Key تم حفظه
        if ($hasApiKey) {
            $decrypted = $model->getDecryptedApiKey();
            if (empty($decrypted)) {
                Log::error('API Key was not saved correctly', ['model_id' => $model->id]);
            } else {
                Log::info('API Key verified after save', ['model_id' => $model->id]);
            }
        }
        
        return $model;
    }

    /**
     * حذف موديل
     */
    public function deleteModel(AIModel $model): bool
    {
        // إذا كان الموديل الافتراضي، تعيين موديل آخر كافتراضي
        if ($model->is_default) {
            $newDefault = AIModel::where('id', '!=', $model->id)
                                ->where('is_active', true)
                                ->orderBy('priority', 'desc')
                                ->first();
            
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return $model->delete();
    }

    /**
     * اختبار الموديل مع API Key مباشر (للاستخدام في الاختبار المؤقت)
     */
    public function testModelWithRawApiKey(array $data, string $rawApiKey): array
    {
        try {
            // التحقق من وجود API Key
            if (!$rawApiKey || trim($rawApiKey) === '') {
                return [
                    'success' => false,
                    'message' => 'API Key غير موجود. يرجى إدخال API Key أولاً.',
                ];
            }

            // التحقق من وجود Model Key
            if (empty($data['model_key'])) {
                return [
                    'success' => false,
                    'message' => 'Model Key غير موجود.',
                ];
            }
            
            // إنشاء موديل مؤقت بدون حفظ
            $tempModel = new AIModel();
            $tempModel->fill($data);
            // تعيين API Key مباشرة للاختبار (بدون تشفير)
            $tempModel->setRawApiKeyForTesting($rawApiKey);
            
            $apiKey = $rawApiKey;

            // إنشاء Provider واختبار الاتصال
            $provider = AIProviderFactory::create($tempModel);
            $startTime = microtime(true);
            $success = $provider->testConnection();
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2); // milliseconds

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'الاتصال ناجح! API Key يعمل بشكل صحيح.',
                    'response_time_ms' => $responseTime,
                    'provider' => $tempModel->provider,
                    'model_key' => $tempModel->model_key,
                ];
            } else {
                // الحصول على رسالة الخطأ من Provider
                $testResult = $provider->chat([
                    ['role' => 'user', 'content' => 'Say "OK" only.']
                ], ['max_tokens' => 10]);
                
                // محاولة الحصول على الخطأ من getLastError أولاً
                $lastError = method_exists($provider, 'getLastError') ? $provider->getLastError() : null;
                $errorMessage = $lastError ?? $testResult['error'] ?? 'فشل الاتصال. يرجى التحقق من API Key و Model Key.';
                $statusCode = $testResult['status_code'] ?? null;
                
                // إضافة معلومات إضافية للرسالة
                $detailedMessage = $errorMessage;
                if ($statusCode) {
                    $detailedMessage .= " (رمز الخطأ: $statusCode)";
                }
                
                // إضافة معلومات عن Model Key و API Key
                $detailedMessage .= "\n\nمعلومات التكوين:";
                $detailedMessage .= "\n- Provider: " . $tempModel->provider;
                $detailedMessage .= "\n- Model Key: " . $tempModel->model_key;
                $detailedMessage .= "\n- API Key موجود: " . (!empty($apiKey) ? 'نعم (' . strlen($apiKey) . ' حرف)' : 'لا');
                $detailedMessage .= "\n- Base URL: " . ($tempModel->base_url ?: 'الافتراضي');
                $detailedMessage .= "\n- API Endpoint: " . ($tempModel->api_endpoint ?: 'الافتراضي');
                
                // إضافة نصائح حسب Provider
                if ($tempModel->provider === 'openai') {
                    $detailedMessage .= "\n\n💡 نصائح:";
                    $detailedMessage .= "\n- تأكد من أن API Key صحيح من: https://platform.openai.com/api-keys";
                    $detailedMessage .= "\n- تأكد من أن Model Key صحيح (مثل: gpt-4, gpt-3.5-turbo)";
                    $detailedMessage .= "\n- تحقق من رصيد OpenAI الخاص بك";
                } elseif ($tempModel->provider === 'google') {
                    $detailedMessage .= "\n\n💡 نصائح:";
                    $detailedMessage .= "\n- تأكد من تفعيل Billing في Google Cloud";
                    $detailedMessage .= "\n- تحقق من أن API Key صحيح من: https://aistudio.google.com/apikey";
                } elseif ($tempModel->provider === 'openrouter') {
                    $detailedMessage .= "\n\n💡 نصائح:";
                    $detailedMessage .= "\n- يمكنك الحصول على API Key مجاني من: https://openrouter.ai/keys";
                    $detailedMessage .= "\n- الموديلات المجانية متاحة فوراً";
                } elseif ($tempModel->provider === 'zai') {
                    $detailedMessage .= "\n\n💡 نصائح:";
                    $detailedMessage .= "\n- تأكد من أن API Key صحيح من: https://z.ai/subscribe";
                    $detailedMessage .= "\n- تأكد من أن Model Key صحيح (مثل: glm-4.7, glm-4)";
                }
                
                return [
                    'success' => false,
                    'message' => $detailedMessage,
                    'response_time_ms' => $responseTime,
                    'provider' => $tempModel->provider,
                    'model_key' => $tempModel->model_key,
                    'status_code' => $statusCode,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error testing model with raw API key: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'خطأ في الاختبار: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * الحصول على الموديل الافتراضي
     */
    public function getDefaultModel(): ?AIModel
    {
        return AIModel::default()->active()->first() 
            ?? AIModel::active()->orderBy('priority', 'desc')->first();
    }

    /**
     * الحصول على أفضل موديل لقدرة معينة
     */
    public function getBestModelFor(string $capability): ?AIModel
    {
        // أولاً: الموديل الافتراضي إذا كان يدعم القدرة
        $default = $this->getDefaultModel();
        if ($default && $default->canHandle($capability)) {
            return $default;
        }

        // ثانياً: البحث عن موديل نشط يدعم القدرة حسب الأولوية
        return AIModel::active()
                     ->byCapability($capability)
                     ->orderBy('priority', 'desc')
                     ->first();
    }

    /**
     * التبديل بين الموديلات
     */
    public function switchModel(AIModel $model): bool
    {
        if (!$model->is_active) {
            return false;
        }

        // تعيين هذا الموديل كافتراضي
        AIModel::where('is_default', true)->update(['is_default' => false]);
        return $model->update(['is_default' => true]);
    }

    /**
     * الحصول على الموديلات المتاحة
     */
    public function getAvailableModels(string $capability = 'all'): Collection
    {
        $query = AIModel::active();

        if ($capability !== 'all') {
            $query->byCapability($capability);
        }

        return $query->orderBy('priority', 'desc')->orderBy('name')->get();
    }
}

