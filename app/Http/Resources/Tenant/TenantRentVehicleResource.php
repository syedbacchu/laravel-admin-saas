<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantRentVehicleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'vehicle_name' => $this->vehicle_name,
            'vendor_id' => $this->vendor_id,
            'vendor' => $this->vendor,
            'driver_name' => $this->driver_name,
            'vendor_driver' => $this->vendor_driver,
            'vehicle_category_id' => $this->vehicle_category_id,
            'vehicle_category' => $this->vehicle_category,
            'vehicle_size_id' => $this->vehicle_size_id,
            'vehicle_size' => $this->vehicle_size,
            'registration_number' => $this->registration_number,
            'registration_serial_id' => $this->registration_serial_id,
            'registration_serial' => $this->registration_serial,
            'registration_zone_id' => $this->registration_zone_id,
            'registration_zone' => $this->registration_zone,
            'status' => (int) $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
