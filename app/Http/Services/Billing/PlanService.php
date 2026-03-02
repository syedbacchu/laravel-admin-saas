<?php

namespace App\Http\Services\Billing;

use App\Http\Requests\Billing\PlanCreateRequest;
use App\Http\Services\BaseService;
use App\Models\Feature;
use App\Models\Language;
use App\Models\Plan;
use App\Models\PlanFeatureValue;
use App\Models\PlanPricing;
use Illuminate\Support\Facades\DB;
use Throwable;

class PlanService extends BaseService implements PlanServiceInterface
{
    protected PlanRepositoryInterface $planRepository;

    public function __construct(PlanRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->planRepository = $repository;
    }

    public function getDataTableData($request): array
    {
        $data = $this->planRepository->planList($request);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function planCreateData($request): array
    {
        $features = Feature::query()
            ->where('is_active', 1)
            ->orderBy('key')
            ->get(['id', 'key', 'name', 'value_type']);

        $languages = Language::query()
            ->forInput()
            ->get(['id', 'name', 'native_name', 'code', 'direction', 'is_default']);
        $defaultLanguage = $languages->firstWhere('is_default', 1);

        $pricingTerms = [1, 3, 6, 12];

        return $this->sendResponse(true, '', [
            'features' => $features,
            'languages' => $languages,
            'default_language' => $defaultLanguage,
            'pricing_terms' => $pricingTerms,
        ]);
    }

    public function planEditData($id): array
    {
        $item = $this->planRepository->find((int) $id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        $item->load(['featureValues', 'pricings', 'translations']);

        return $this->sendResponse(true, '', $item);
    }

    public function storeOrUpdatePlan(PlanCreateRequest $request): array
    {
        try {
            return DB::transaction(function () use ($request) {
                $languages = Language::query()
                    ->forInput()
                    ->get(['id', 'is_default']);
                $defaultLanguage = $languages->firstWhere('is_default', 1);
                if (!$defaultLanguage) {
                    return $this->sendResponse(false, __('Default language is missing'));
                }

                $translations = (array) $request->input('translations', []);
                $defaultInput = (array) ($translations[$defaultLanguage->id] ?? []);
                $defaultName = trim((string) ($defaultInput['name'] ?? ''));
                $defaultSubtitle = trim((string) ($defaultInput['subtitle'] ?? ''));

                $data = [
                    'name' => $defaultName,
                    'subtitle' => $defaultSubtitle !== '' ? $defaultSubtitle : null,
                    'slug' => $request->slug,
                    'sort_order' => (int) ($request->sort_order ?? 0),
                    'is_active' => (int) ($request->is_active ?? 1),
                ];

                if ($request->edit_id) {
                    $plan = $this->planRepository->find((int) $request->edit_id);
                    if (!$plan) {
                        return $this->sendResponse(false, __('Data not found'));
                    }

                    $this->planRepository->update($plan->id, $data);
                    $plan = $this->planRepository->find((int) $plan->id);
                    $message = __('Plan updated successfully');
                } else {
                    $plan = $this->planRepository->createPlan($data);
                    $message = __('Plan created successfully');
                }

                $this->syncTranslations($plan, $translations, $languages->pluck('id')->map(fn ($id) => (int) $id)->all(), (int) $defaultLanguage->id);
                $this->syncFeatureValues($plan->id, $request);
                $this->syncPricings($plan->id, $request);
                $this->clearTenantFeatureCacheForPlan($plan->id);

                return $this->sendResponse(true, $message, $plan->fresh(['featureValues.feature', 'pricings', 'translations']));
            });
        } catch (Throwable $e) {
            logStore('PlanService storeOrUpdatePlan', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function deletePlan($id): array
    {
        $item = $this->planRepository->find((int) $id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        try {
            $this->planRepository->delete((int) $id);
            return $this->sendResponse(true, __('Data deleted successfully'));
        } catch (Throwable $e) {
            return $this->sendResponse(false, __('This plan cannot be deleted because it is used by subscriptions'));
        }
    }

    protected function syncFeatureValues(int $planId, PlanCreateRequest $request): void
    {
        $assigned = array_keys((array) $request->input('feature_assign', []));
        $assignedIds = array_map('intval', $assigned);

        PlanFeatureValue::query()
            ->where('plan_id', $planId)
            ->whereNotIn('feature_id', $assignedIds ?: [0])
            ->delete();

        if (empty($assignedIds)) {
            return;
        }

        $features = Feature::query()
            ->whereIn('id', $assignedIds)
            ->get(['id', 'value_type']);

        foreach ($features as $feature) {
            $input = (array) $request->input("feature_values.{$feature->id}", []);
            $payload = $this->buildFeatureValuePayload($feature->value_type, $input);

            PlanFeatureValue::query()->updateOrCreate(
                [
                    'plan_id' => $planId,
                    'feature_id' => $feature->id,
                ],
                $payload
            );
        }
    }

    protected function syncPricings(int $planId, PlanCreateRequest $request): void
    {
        $pricings = (array) $request->input('pricings', []);
        $savedTerms = [];

        foreach ($pricings as $termKey => $pricing) {
            $row = (array) $pricing;
            $termMonths = (int) ($row['term_months'] ?? $termKey);
            if ($termMonths <= 0) {
                continue;
            }

            $hasRowInput = array_key_exists('base_amount', $row) || array_key_exists('is_active', $row);
            if (!$hasRowInput) {
                continue;
            }

            $baseAmount = (float) ($row['base_amount'] ?? 0);
            $discountType = $row['discount_type'] ?? 'percent';
            $discountValue = (float) ($row['discount_value'] ?? 0);
            $isActive = (int) ($row['is_active'] ?? 0);
            $currency = trim((string) ($row['currency'] ?? 'BDT'));
            $finalAmount = $this->calculateFinalAmount($baseAmount, $discountType, $discountValue);

            PlanPricing::query()->updateOrCreate(
                [
                    'plan_id' => $planId,
                    'term_months' => $termMonths,
                ],
                [
                    'base_amount' => $baseAmount,
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                    'final_amount' => $finalAmount,
                    'currency' => $currency !== '' ? $currency : 'BDT',
                    'is_active' => $isActive,
                ]
            );

            $savedTerms[] = $termMonths;
        }

        PlanPricing::query()
            ->where('plan_id', $planId)
            ->whereNotIn('term_months', $savedTerms ?: [0])
            ->delete();
    }

    protected function buildFeatureValuePayload(string $type, array $input): array
    {
        $payload = [
            'value_bool' => null,
            'value_int' => null,
            'value_decimal' => null,
            'value_text' => null,
            'value_json' => null,
        ];

        if ($type === 'boolean') {
            $payload['value_bool'] = (int) ($input['value_bool'] ?? 0) === 1;
            return $payload;
        }

        if ($type === 'integer') {
            $payload['value_int'] = isset($input['value_int']) && $input['value_int'] !== '' ? (int) $input['value_int'] : null;
            return $payload;
        }

        if ($type === 'decimal') {
            $payload['value_decimal'] = isset($input['value_decimal']) && $input['value_decimal'] !== '' ? (float) $input['value_decimal'] : null;
            return $payload;
        }

        if ($type === 'json') {
            $jsonText = trim((string) ($input['value_json'] ?? ''));
            if ($jsonText === '') {
                $payload['value_json'] = null;
                return $payload;
            }

            $decoded = json_decode($jsonText, true);
            $payload['value_json'] = json_last_error() === JSON_ERROR_NONE ? $decoded : ['raw' => $jsonText];
            return $payload;
        }

        $payload['value_text'] = isset($input['value_text']) ? trim((string) $input['value_text']) : null;
        return $payload;
    }

    protected function calculateFinalAmount(float $baseAmount, string $discountType, float $discountValue): float
    {
        $baseAmount = max(0, $baseAmount);
        $discountValue = max(0, $discountValue);

        if ($discountType === 'fixed') {
            return max(0, $baseAmount - $discountValue);
        }

        $discountAmount = ($baseAmount * $discountValue) / 100;
        return max(0, $baseAmount - $discountAmount);
    }

    protected function syncTranslations(Plan $plan, array $translations, array $allowedLanguageIds, int $defaultLanguageId): void
    {
        foreach ($translations as $languageId => $row) {
            $langId = (int) $languageId;
            if (!in_array($langId, $allowedLanguageIds, true)) {
                continue;
            }

            $name = trim((string) data_get($row, 'name', ''));
            $subtitle = trim((string) data_get($row, 'subtitle', ''));

            if ($name === '' && $subtitle === '' && $langId !== $defaultLanguageId) {
                $plan->translations()
                    ->where('language_id', $langId)
                    ->delete();
                continue;
            }

            $plan->translations()->updateOrCreate(
                ['language_id' => $langId],
                [
                    'name' => $name !== '' ? $name : $plan->name,
                    'subtitle' => $subtitle !== '' ? $subtitle : null,
                ]
            );
        }
    }

    protected function clearTenantFeatureCacheForPlan(int $planId): void
    {
        $tenantIds = DB::table('subscriptions')
            ->where('plan_id', $planId)
            ->whereIn('status', ['trialing', 'active', 'past_due'])
            ->pluck('tenant_id')
            ->unique()
            ->values();

        $resolver = app(\App\Http\Services\Tenant\TenantFeatureResolverService::class);
        foreach ($tenantIds as $tenantId) {
            $resolver->clearFeatureCache((int) $tenantId);
        }
    }
}
