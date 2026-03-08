<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantDriverResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'vehicle_id' => $this->vehicle_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'license_no' => $this->license_no,
            'nid_no' => $this->nid_no,
            'joining_date' => $this->joining_date,
            'address' => $this->address,
            'notes' => $this->notes,
            'status' => (int) $this->status,
            'vehicle' => $this->whenLoaded('vehicle', function () {
                return [
                    'id' => $this->vehicle?->id,
                    'registration_no' => $this->vehicle?->registration_no,
                    'vehicle_type' => $this->vehicle?->vehicle_type,
                    'brand' => $this->vehicle?->brand,
                    'model' => $this->vehicle?->model,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
