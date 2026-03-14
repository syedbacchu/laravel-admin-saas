<?php

namespace App\Http\Services\PricingPlan;

use App\Http\Services\BaseService;
use App\Models\Plan;
use App\Models\PlanFeatureValue;
use App\Support\LanguageResolver;
use App\Support\ModelTranslationResolver;
use Illuminate\Http\Request;

class PricingPlanService extends BaseService implements PricingPlanServiceInterface
{
    protected PricingPlanRepositoryInterface $pricingPlanRepository;

    public function __construct(PricingPlanRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->pricingPlanRepository = $repository;
    }

    public function getPublicPricingPlanList(Request $request): array
    {
        [$languageId, $languageCode] = $this->resolveLanguageContext($request);

        $data = $this->pricingPlanRepository->pricingPlanList($request);

        if (isset($data['data']) && is_iterable($data['data'])) {
            $data['data'] = collect($data['data'])
                ->map(fn (Plan $plan) => $this->mapPlanData($plan, $languageId))
                ->values()
                ->all();
        }

        $data['lang'] = $languageCode;

        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function getPublicPricingPlanDetails(Request $request, string $identifier): array
    {
        [$languageId, $languageCode] = $this->resolveLanguageContext($request);

        $item = $this->pricingPlanRepository->findPublicPricingPlanByIdentifier($identifier);
        if (!$item) {
            return $this->sendResponse(false, __('Pricing plan not found'), [], 404, __('Pricing plan not found'));
        }

        return $this->sendResponse(true, __('Data get successfully.'), [
            'lang' => $languageCode,
            'plan' => $this->mapPlanData($item, $languageId),
        ]);
    }

    protected function resolveLanguageContext(Request $request): array
    {
        $language = LanguageResolver::resolveFromRequest($request, 'lang', 'en');
        $languageId = (int) ($language['id'] ?? 0);
        $languageCode = (string) ($language['code'] ?? 'en');

        return [$languageId, $languageCode];
    }

    protected function mapPlanData(Plan $plan, int $languageId): array
    {
        return [
            'id' => (int) $plan->id,
            'slug' => (string) $plan->slug,
            'name' => (string) ModelTranslationResolver::getValue($plan, 'translations', $languageId, 'name', 'name', ''),
            'subtitle' => ModelTranslationResolver::getValue($plan, 'translations', $languageId, 'subtitle', 'subtitle'),
            'sort_order' => (int) $plan->sort_order,
            'pricings' => $plan->pricings
                ->map(function ($pricing) {
                    return [
                        'id' => (int) $pricing->id,
                        'term_months' => (int) $pricing->term_months,
                        'base_amount' => (float) $pricing->base_amount,
                        'discount_type' => (string) $pricing->discount_type,
                        'discount_value' => (float) $pricing->discount_value,
                        'final_amount' => (float) $pricing->final_amount,
                        'currency' => (string) $pricing->currency,
                    ];
                })
                ->values()
                ->all(),
            'features' => $plan->featureValues
                ->map(function (PlanFeatureValue $featureValue) use ($languageId) {
                    $feature = $featureValue->feature;
                    if (!$feature) {
                        return null;
                    }

                    return [
                        'id' => (int) $feature->id,
                        'key' => (string) $feature->key,
                        'name' => (string) ModelTranslationResolver::getValue($feature, 'translations', $languageId, 'name', 'name', ''),
                        'description' => ModelTranslationResolver::getValue($feature, 'translations', $languageId, 'description', 'description'),
                        'value_type' => (string) $feature->value_type,
                        'value' => $this->resolveFeatureValue($featureValue, (string) $feature->value_type),
                    ];
                })
                ->filter()
                ->values()
                ->all(),
        ];
    }

    protected function resolveFeatureValue(PlanFeatureValue $featureValue, string $valueType): mixed
    {
        if ($valueType === 'boolean') {
            return (bool) $featureValue->value_bool;
        }

        if ($valueType === 'integer') {
            return $featureValue->value_int !== null ? (int) $featureValue->value_int : null;
        }

        if ($valueType === 'decimal') {
            return $featureValue->value_decimal !== null ? (float) $featureValue->value_decimal : null;
        }

        if ($valueType === 'json') {
            return $featureValue->value_json;
        }

        return $featureValue->value_text;
    }
}
