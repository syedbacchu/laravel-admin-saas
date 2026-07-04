<?php

namespace App\Http\Requests\TenantApi;

use App\Models\TenantPayrollSalarySheet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TenantPayrollSalaryPaymentCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'salary_sheet_id' => ['required', 'integer'],
            'payment_amount' => ['required', 'numeric', 'min:0.01', 'regex:/^\d+(\.\d{1,2})?$/'],
            'payment_date' => ['nullable', 'date', 'before_or_equal:today'],
            'office_id' => ['required', 'integer', 'exists:tenant.offices,id'],
            'payment_method' => ['nullable', 'string', 'in:cash,bank,check,mobile_banking,other'],
            'transaction_id' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'attachment' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'integer', 'in:0,1'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $salarySheetId = (int) $this->input('salary_sheet_id');
            $paymentAmount = (float) $this->input('payment_amount');

            $salarySheet = TenantPayrollSalarySheet::find($salarySheetId);

            if (!$salarySheet) {
                return;
            }

            $totalPaid = \App\Models\TenantPayrollSalaryPayment::where('salary_sheet_id', $salarySheetId)
                ->sum('payment_amount');

            $netPayable = (float) $salarySheet->net_payable;
            $dueAmount = $netPayable - (float) $totalPaid;

            if ($paymentAmount > $dueAmount) {
                $validator->errors()->add('payment_amount',
                    'Payment amount cannot exceed due amount. Maximum payable: ' . number_format($dueAmount, 2)
                );
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'success' => false,
            'message' => __('Validation failed'),
            'errors' => $validator->errors(),
        ], 422);

        throw new HttpResponseException($response);
    }

    public function messages(): array
    {
        return [
            'salary_sheet_id.required' => __('Salary sheet ID is required'),
            'salary_sheet_id.exists' => __('Salary sheet not found'),
            'payment_amount.required' => __('Payment amount is required'),
            'payment_amount.numeric' => __('Payment amount must be a number'),
            'payment_amount.min' => __('Payment amount must be greater than zero'),
            'payment_amount.regex' => __('Payment amount format is invalid'),
            'payment_date.date' => __('Payment date must be a valid date'),
            'payment_date.before_or_equal' => __('Payment date cannot be in the future'),
            'office_id.required' => __('Office ID is required'),
            'office_id.exists' => __('Office not found'),
            'payment_method.in' => __('Invalid payment method'),
        ];
    }
}
