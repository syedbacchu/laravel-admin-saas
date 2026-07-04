<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantSupervisorResource extends JsonResource
{
    public function toArray($request): array
    {
        $vehicles = collect($this->vehicles ?? [])->map(function ($vehicle) {
            return [
                'id' => $vehicle->id,
                'registration_no' => $vehicle->registration_no,
                'vehicle_name' => $vehicle->vehicle_name,
                'vehicle_type' => $vehicle->vehicle_type,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
            ];
        })->values();

        return [
            'id' => $this->id,
            'vehicle_ids' => $vehicles->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'name' => $this->name,
            'mobile' => $this->mobile,
            'nid_no' => $this->nid,
            'image' => $this->image,
            'joining_date' => $this->joining_date,
            'address' => $this->address,
            'vehicle_category_id' => $this->vehicle_category_id,
            'vehicle_category' => $this->vehicle_category,
            'basic_salary' => $this->basic_salary,
            'house_rent' => $this->house_rent,
            'medical' => $this->medical,
            'allowance' => $this->allowance,
            'extra_allowance' => $this->extra_allowance,
            'conveyance' => $this->conveyance,
            'gross_salary' => $this->gross_salary,
            'vehicles' => $vehicles->all(),
            'status' => (int) $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
