<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantPayrollAdvanceSalaryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'added_by' => $this->added_by,
            'updated_by' => $this->updated_by,
            'date' => $this->date,
            'employee_id' => $this->employee_id,
            'employee' => $this->employee,
            'advance_amount' => $this->advance_amount,
            'salary_month' => $this->salary_month,
            'after_adjustment_amount' => $this->after_adjustment_amount,
            'status' => $this->status,
            'created_by_user' => $this->created_by_user,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
