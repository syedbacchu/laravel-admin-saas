<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantSalaryExpenseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'salary_month' => $this->salary_month,
            'paid_to_user_id' => $this->paid_to_user_id,
            'paid_to_user' => $this->paid_to_user,
            'category' => $this->category,
            'office_id' => $this->office_id,
            'office' => $this->office,
            'amount' => $this->amount,
            'remarks' => $this->remarks,
            'attachment' => $this->attachment,
            'status' => (int) $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
