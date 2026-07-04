<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantPayrollLoanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'added_by' => $this->added_by,
            'updated_by' => $this->updated_by,
            'loan_date' => $this->loan_date,
            'employee_id' => $this->employee_id,
            'employee' => $this->employee,
            'loan_amount' => $this->loan_amount,
            'monthly_deduction' => $this->monthly_deduction,
            'after_adjustment_amount' => $this->after_adjustment_amount,
            'remaining_balance' => $this->remaining_balance,
            'paid_amount' => $this->paid_amount,
            'status' => $this->status,
            'created_by_user' => $this->created_by_user,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
