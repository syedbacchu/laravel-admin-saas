<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantSubscriptionResource extends JsonResource
{
    public function toArray($request): array
    {
        $pricing = $this->relationLoaded('pricing') ? $this->pricing : null;
        $plan = $this->relationLoaded('plan') ? $this->plan : null;

        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'plan_id' => $this->plan_id,
            'plan_name' => $plan?->name,
            'status' => $this->status,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'grace_ends_at' => $this->grace_ends_at,
            'auto_renew' => (int) $this->auto_renew,
            'pricing' => $pricing ? [
                'id' => $pricing->id,
                'term_months' => $pricing->term_months,
                'final_amount' => (float) $pricing->final_amount,
                'currency' => $pricing->currency,
            ] : null,
        ];
    }
}

