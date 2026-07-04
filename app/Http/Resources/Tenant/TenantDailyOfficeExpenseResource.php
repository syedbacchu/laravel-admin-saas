<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantDailyOfficeExpenseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'paid_to' => $this->paid_to,
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
