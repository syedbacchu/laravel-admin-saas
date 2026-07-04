<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantFundTransferResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'office_id' => $this->office_id,
            'branch_name' => $this->office['branch_name'] ?? null,
            'office' => $this->office,
            'person_name' => $this->person_name,
            'cash_type' => $this->cash_type,
            'amount' => $this->amount,
            'bank_name' => $this->bank_name,
            'purpose' => $this->purpose,
            'status' => (int) $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
