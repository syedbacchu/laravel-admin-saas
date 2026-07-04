<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantPayrollSalaryPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'payment_date' => (string) $this->payment_date,
            'salary_sheet_id' => (int) $this->salary_sheet_id,
            'employee_id' => (int) $this->employee_id,
            'salary_month' => (string) $this->salary_month,
            'total_payable' => (float) $this->total_payable,
            'payment_amount' => (float) $this->payment_amount,
            'previous_paid' => (float) $this->previous_paid,
            'remaining_due' => (float) $this->remaining_due,
            'office_id' => (int) $this->office_id,
            'payment_method' => (string) $this->payment_method,
            'transaction_id' => $this->transaction_id,
            'remarks' => $this->remarks,
            'attachment' => $this->attachment,
            'status' => (int) $this->status,
            'salary_expense_id' => $this->when(isset($this->salary_expense_id), $this->salary_expense_id),
            'salary_expense' => $this->when($this->relationLoaded('salaryExpense'), function () {
                return [
                    'id' => (int) $this->salaryExpense->id,
                    'date' => (string) $this->salaryExpense->date,
                    'category' => (string) $this->salaryExpense->category,
                    'amount' => (float) $this->salaryExpense->amount,
                ];
            }),
            'created_by_user' => $this->when(isset($this->created_by_user), $this->created_by_user),
            'updated_by_user' => $this->when(isset($this->updated_by_user), $this->updated_by_user),
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
        ];
    }
}
