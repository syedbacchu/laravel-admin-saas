<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TripVendorPaymentCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'vendor_id' => $this->input('vendor_id'),
            'amount' => $this->input('amount'),
            'payment_method' => $this->input('payment_method', 'cash'),
            'note' => trim((string) $this->input('note', '')),
            'bill_ref' => trim((string) $this->input('bill_ref', '')),
            'bill_document' => $this->input('bill_document'),
            'date' => $this->input('date', now()->format('Y-m-d')),
        ]);
    }

    public function rules(): array
    {
        return [
            'vendor_id' => ['required', 'integer', 'exists:tenant.vendors,id'],
            'amount' => ['required', 'numeric', 'min:0', 'not_in:0'],
            'payment_method' => ['required', 'string', 'in:cash,bank_transfer,check,mobile_banking,other'],
            'note' => ['nullable', 'string', 'max:500'],
            'bill_ref' => ['nullable', 'string', 'max:120'],
            'bill_document' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'vendor_id.required' => 'The vendor field is required.',
            'vendor_id.exists' => 'The selected vendor is invalid.',
            'amount.required' => 'The amount field is required.',
            'amount.numeric' => 'The amount must be a number.',
            'amount.min' => 'The amount must be at least 0.',
            'amount.not_in' => 'The amount cannot be zero.',
            'payment_method.required' => 'The payment method field is required.',
            'payment_method.in' => 'The payment method must be one of: cash, bank transfer, check, mobile banking, other.',
            'note.max' => 'The note may not be greater than 500 characters.',
            'bill_ref.max' => 'The bill reference may not be greater than 120 characters.',
            'bill_document.max' => 'The bill document may not be greater than 255 characters.',
            'date.date' => 'The date must be a valid date.',
            'date.before_or_equal' => 'The date cannot be in the future.',
        ];
    }

    public function attributes(): array
    {
        return [
            'vendor_id' => 'vendor',
            'amount' => 'payment amount',
            'payment_method' => 'payment method',
            'note' => 'note',
            'bill_ref' => 'bill reference',
            'bill_document' => 'bill document',
            'date' => 'payment date',
        ];
    }
}
