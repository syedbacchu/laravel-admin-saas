<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TripPaymentReceiveCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_id' => $this->input('customer_id'),
            'office_id' => $this->input('office_id'),
            'amount' => $this->input('amount'),
            'cash_type' => $this->input('cash_type', 'cash'),
            'note' => trim((string) $this->input('note', '')),
            'bill_ref' => trim((string) $this->input('bill_ref', '')),
            'bill_document' => $this->input('bill_document'),
            'date' => $this->input('date', now()->format('Y-m-d')),
        ]);
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:tenant.customers,id'],
            'office_id' => ['required', 'integer', 'exists:tenant.offices,id'],
            'amount' => ['required', 'numeric', 'min:0', 'not_in:0'],
            'cash_type' => ['required', 'string', 'in:cash,bank,other'],
            'note' => ['nullable', 'string', 'max:500'],
            'bill_ref' => ['nullable', 'string', 'max:120'],
            'bill_document' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'The customer field is required.',
            'customer_id.exists' => 'The selected customer is invalid.',
            'office_id.required' => 'The office/branch field is required.',
            'office_id.exists' => 'The selected office/branch is invalid.',
            'amount.required' => 'The amount field is required.',
            'amount.numeric' => 'The amount must be a number.',
            'amount.min' => 'The amount must be at least 0.',
            'amount.not_in' => 'The amount cannot be zero.',
            'cash_type.required' => 'The cash type field is required.',
            'cash_type.in' => 'The cash type must be one of: cash, bank, other.',
            'date.date' => 'The date must be a valid date.',
            'date.before_or_equal' => 'The date cannot be in the future.',
        ];
    }
}
