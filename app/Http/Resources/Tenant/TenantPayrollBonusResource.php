<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantPayrollBonusResource extends JsonResource
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
            'bonus_amount' => $this->bonus_amount,
            'salary_month' => $this->salary_month,
            'status' => $this->status,
            'created_by_user' => $this->created_by_user,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
