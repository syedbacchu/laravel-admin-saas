<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantRoutePricingResource extends JsonResource
{
    public function toArray($request): array
    {
        $loadArea = $this->load_area;

        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'customer' => $this->customer,
            'vehicle_category_id' => $this->vehicle_category_id,
            'vehicle_category' => $this->vehicle_category,
            'load_area_id' => $this->load_area_id,
            'load_area_name' => $loadArea['name'] ?? $this->load_area_name,
            'load_area_address' => $loadArea['address'] ?? $this->load_area_address,
            'load_area' => $loadArea,
            'unload_area_id' => $this->unload_area_id,
            'unload_area' => $this->unload_area,
            'vehicle_size_id' => $this->vehicle_size_id,
            'vehicle_size' => $this->vehicle_size,
            'rate' => $this->rate,
            'status' => (int) $this->status,
            'distance' => $this->distance,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
