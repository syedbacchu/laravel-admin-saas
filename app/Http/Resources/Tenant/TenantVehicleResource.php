<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantVehicleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'registration_no' => $this->registration_no,
            'vehicle_type' => $this->vehicle_type,
            'brand' => $this->brand,
            'model' => $this->model,
            'manufacturing_year' => $this->manufacturing_year,
            'color' => $this->color,
            'notes' => $this->notes,
            'status' => (int) $this->status,
            'driver_count' => isset($this->drivers_count) ? (int) $this->drivers_count : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
