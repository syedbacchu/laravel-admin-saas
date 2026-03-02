<?php

namespace App\Http\Requests\Billing;

use App\Http\Requests\BaseFormRequest;
use App\Models\PaymentMethod;
use App\Models\Subscription;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SubscriptionPaymentCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'exists:tenants,id'],
            'subscription_id' => ['required', 'exists:subscriptions,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'max:10'],
            'status' => ['nullable', Rule::in(['pending', 'verified', 'rejected'])],
            'paid_at' => ['nullable', 'date'],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'method_details.mobile_number' => ['nullable', 'string', 'max:60'],
            'method_details.account_number' => ['nullable', 'string', 'max:120'],
            'method_details.bank_name' => ['nullable', 'string', 'max:160'],
            'method_details.branch_name' => ['nullable', 'string', 'max:160'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $subscription = Subscription::query()->find((int) $this->input('subscription_id'));
            $tenantId = (int) $this->input('tenant_id');

            if ($subscription && (int) $subscription->tenant_id !== $tenantId) {
                $validator->errors()->add('subscription_id', __('Selected subscription does not belong to tenant'));
            }

            $paymentMethod = PaymentMethod::query()->find((int) $this->input('payment_method_id'));
            if ($paymentMethod && (int) $paymentMethod->is_active !== 1 && !$this->edit_id) {
                $validator->errors()->add('payment_method_id', __('Inactive payment method cannot be used'));
            }

            if ($paymentMethod && $paymentMethod->code === 'bank_payment') {
                $bankName = trim((string) $this->input('method_details.bank_name', ''));
                if ($bankName === '') {
                    $validator->errors()->add('method_details.bank_name', __('Bank name is required for bank payment'));
                }
            }
        });
    }
}

