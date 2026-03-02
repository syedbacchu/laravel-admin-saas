<?php

namespace App\Http\Requests\Billing;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class SubscriptionCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'exists:tenants,id'],
            'plan_id' => ['required', 'exists:plans,id'],
            'plan_pricing_id' => ['required', 'exists:plan_pricings,id'],
            'status' => ['nullable', Rule::in(['trialing', 'active', 'past_due', 'canceled', 'expired'])],
            'starts_at' => ['nullable', 'date'],
            'auto_renew' => ['nullable', 'in:0,1'],
            'grace_ends_at' => ['nullable', 'date'],
        ];
    }
}
