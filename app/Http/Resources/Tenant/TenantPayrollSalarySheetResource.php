<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantPayrollSalarySheetResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'generated_salary_id' => $this->generated_salary_id,
            'employee_id' => $this->employee_id,
            'employee' => $this->employee,
            'working_day' => (int) $this->working_day,
            'designation' => $this->designation,
            'basic_salary' => $this->basic_salary,
            'house_rent' => $this->house_rent,
            'conveyance' => $this->conveyance,
            'medical' => $this->medical,
            'allowance' => $this->allowance,
            'extra_allowance' => $this->extra_allowance,
            'gross_salary' => $this->gross_salary,
            'bonus' => $this->bonus,
            'total_earnings' => $this->total_earnings,
            'advance_deduction' => $this->advance_deduction,
            'loan_deduction' => $this->loan_deduction,
            'total_deduction' => $this->total_deduction,
            'net_payable' => $this->net_payable,
            'paid_amount' => $this->paid_amount,
            'due_amount' => $this->due_amount,
            'payment_status' => $this->payment_status,
            'paid_date' => $this->paid_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
