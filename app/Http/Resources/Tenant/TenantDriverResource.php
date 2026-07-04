<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantDriverResource extends JsonResource
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
            'vehicle_category_id' => $this->vehicle_category_id,
            'vehicle_category' => $this->vehicle_category,
            'name' => $this->name,
            'phone' => $this->phone,
            'mobile' => $this->phone,
            'emergency_contact' => $this->emergency_contact,
            'license_no' => $this->license_no,
            'license_expired_date' => $this->license_expired_date,
            'nid_no' => $this->nid_no,
            'image' => $this->image,
            'joining_date' => $this->joining_date,
            'address' => $this->address,
            'notes' => $this->notes,
            'opening_balance' => $this->opening_balance,
            'status' => (int) $this->status,
            'has_login_account' => (bool) ($this->has_login_account ?? false),
            'login_enabled' => (bool) ($this->login_enabled ?? false),
            'login_account' => $this->login_account,
            'vehicles' => $vehicles->all(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
