<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class PurchasePaymentCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => $this->input('type'),
            'purchase_id' => $this->input('purchase_id'),
            'amount' => $this->input('amount'),
            'payment_method' => $this->input('payment_method', 'cash'),
            'note' => trim((string) $this->input('note', '')),
            'attachment' => $this->input('attachment'),
            'payment_date' => $this->input('payment_date', now()->format('Y-m-d')),
        ]);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:fuel,maintenance,official_product'],
            'purchase_id' => ['required', 'integer', 'min:1'],
            'amount' => ['required', 'numeric', 'min:0', 'not_in:0'],
            'payment_method' => ['required', 'string', 'in:cash,bank_transfer,check,mobile_banking,other'],
            'note' => ['nullable', 'string', 'max:500'],
            'attachment' => ['nullable', 'string', 'max:255'],
            'payment_date' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'The purchase type field is required.',
            'type.in' => 'The type must be one of: fuel, maintenance, official product.',
            'purchase_id.required' => 'The purchase ID field is required.',
            'purchase_id.integer' => 'The purchase ID must be an integer.',
            'purchase_id.min' => 'The purchase ID must be at least 1.',
            'amount.required' => 'The amount field is required.',
            'amount.numeric' => 'The amount must be a number.',
            'amount.min' => 'The amount must be at least 0.',
            'amount.not_in' => 'The amount cannot be zero.',
            'payment_method.required' => 'The payment method field is required.',
            'payment_method.in' => 'The payment method must be one of: cash, bank transfer, check, mobile banking, other.',
            'note.max' => 'The note may not be greater than 500 characters.',
            'attachment.max' => 'The attachment may not be greater than 255 characters.',
            'payment_date.date' => 'The payment date must be a valid date.',
            'payment_date.before_or_equal' => 'The payment date cannot be in the future.',
        ];
    }

    public function attributes(): array
    {
        return [
            'type' => 'purchase type',
            'purchase_id' => 'purchase ID',
            'amount' => 'payment amount',
            'payment_method' => 'payment method',
            'note' => 'note',
            'attachment' => 'attachment/document',
            'payment_date' => 'payment date',
        ];
    }
}
