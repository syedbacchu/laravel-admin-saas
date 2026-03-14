<?php

namespace App\Http\Services\PricingPlan;

use App\Http\Repositories\BaseRepository;
use App\Models\Plan;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PricingPlanRepository extends BaseRepository implements PricingPlanRepositoryInterface
{
    public function __construct(Plan $model)
    {
        parent::__construct($model);
    }

    public function pricingPlanList(Request $request): array
    {
        $request->merge([
            'orderColumn' => $request->get('orderColumn', 'sort_order'),
            'orderBy' => $request->get('orderBy', 'asc'),
        ]);

        return DataListManager::list(
            request: $request,
            query: $this->publicPlanQuery(),
            searchable: [
                'name',
                'slug',
                'subtitle',
            ],
            select: [
                'id',
                'name',
                'slug',
                'subtitle',
                'sort_order',
                'is_active',
                'created_at',
            ],
        );
    }

    public function findPublicPricingPlanByIdentifier(string $identifier): ?Plan
    {
        return $this->publicPlanQuery()
            ->where(function ($query) use ($identifier) {
                $query->where('slug', $identifier);

                if (is_numeric($identifier)) {
                    $query->orWhere('id', (int) $identifier);
                }
            })
            ->first([
                'id',
                'name',
                'slug',
                'subtitle',
                'sort_order',
                'is_active',
                'created_at',
            ]);
    }

    protected function publicPlanQuery(): Builder
    {
        return Plan::query()
            ->where('is_active', 1)
            ->whereHas('pricings', fn ($query) => $query->where('is_active', 1))
            ->with([
                'translations:id,plan_id,language_id,name,subtitle',
                'pricings' => function ($query) {
                    $query->where('is_active', 1)
                        ->orderBy('term_months')
                        ->select([
                            'id',
                            'plan_id',
                            'term_months',
                            'base_amount',
                            'discount_type',
                            'discount_value',
                            'final_amount',
                            'currency',
                        ]);
                },
                'featureValues' => function ($query) {
                    $query->whereHas('feature', fn ($subQuery) => $subQuery->where('is_active', 1))
                        ->with([
                            'feature' => function ($featureQuery) {
                                $featureQuery->select([
                                    'id',
                                    'key',
                                    'name',
                                    'description',
                                    'value_type',
                                    'is_active',
                                ])->with('translations:id,feature_id,language_id,name,description');
                            },
                        ])->orderBy('feature_id')
                        ->select([
                            'id',
                            'plan_id',
                            'feature_id',
                            'value_bool',
                            'value_int',
                            'value_decimal',
                            'value_text',
                            'value_json',
                        ]);
                },
            ]);
    }
}
