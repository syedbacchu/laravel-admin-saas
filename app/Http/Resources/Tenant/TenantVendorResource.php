<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantVendorResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'mobile' => $this->mobile,
            'date' => $this->date,
            'vehicle_category_id' => $this->vehicle_category_id,
            'vehicle_category' => $this->vehicle_category,
            'work_area' => $this->work_area,
            'opening_balance' => $this->opening_balance,
            'status' => (int) $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

