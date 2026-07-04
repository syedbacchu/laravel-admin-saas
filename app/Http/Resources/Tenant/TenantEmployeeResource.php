<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantEmployeeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'gender' => $this->gender,
            'blood_group' => $this->blood_group,
            'birth_date' => $this->birth_date,
            'join_date' => $this->join_date,
            'nid' => $this->nid,
            'designation' => $this->designation,
            'address' => $this->address,
            'basic_salary' => $this->basic_salary,
            'house_rent' => $this->house_rent,
            'medical' => $this->medical,
            'allowance' => $this->allowance,
            'extra_allowance' => $this->extra_allowance,
            'conveyance' => $this->conveyance,
            'gross_salary' => $this->gross_salary,
            'image' => $this->image,
            'status' => (int) $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

