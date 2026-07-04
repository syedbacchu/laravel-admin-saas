<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantVehicleResource extends JsonResource
{
    public function toArray($request): array
    {
        $drivers = collect($this->drivers ?? [])->values();
        $helpers = collect($this->helpers ?? [])->values();
        $supervisors = collect($this->supervisors ?? [])->values();

        return [
            'id' => $this->id,
            'date' => $this->date,
            'vehicle_name' => $this->vehicle_name,
            'driver_ids' => $drivers->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'drivers' => $drivers->all(),
            'helper_ids' => $helpers->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'helpers' => $helpers->all(),
            'supervisor_ids' => $supervisors->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'supervisors' => $supervisors->all(),
            'vehicle_category_id' => $this->vehicle_category_id,
            'vehicle_category' => $this->vehicle_category,
            'vehicle_size_id' => $this->vehicle_size_id,
            'vehicle_size' => $this->vehicle_size,
            'vehicle_kpl' => $this->vehicle_kpl,
            'fuel_capacity' => $this->fuel_capacity,
            'registration_number' => $this->registration_number ?: $this->registration_no,
            'registration_serial_id' => $this->registration_serial_id,
            'registration_serial' => $this->registration_serial,
            'registration_zone_id' => $this->registration_zone_id,
            'registration_zone' => $this->registration_zone,
            'registration_expired_date' => $this->registration_expired_date,
            'tax_expired_date' => $this->tax_expired_date,
            'road_permit_expired_date' => $this->road_permit_expired_date,
            'fitness_expired_date' => $this->fitness_expired_date,
            'insurance_expired_date' => $this->insurance_expired_date,
            'registration_no' => $this->registration_no,
            'vehicle_type' => $this->vehicle_type,
            'brand' => $this->brand,
            'model' => $this->model,
            'image' => $this->image,
            'manufacturing_year' => $this->manufacturing_year,
            'color' => $this->color,
            'notes' => $this->notes,
            'status' => (int) $this->status,
            'driver_count' => isset($this->drivers_count) ? (int) $this->drivers_count : null,
            'helper_count' => isset($this->helpers_count) ? (int) $this->helpers_count : null,
            'supervisor_count' => isset($this->supervisors_count) ? (int) $this->supervisors_count : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
