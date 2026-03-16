<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantUserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'image' => $this->image,
            'language' => $this->language,
            'address' => $this->address,
            'status' => $this->status,
            'enable_login' => $this->enable_login,
            'role_module' => $this->role_module,
            'role_id' => $this->role_id,
            'user_type' => $this->user_type,
            'tenant_driver_id' => $this->tenant_driver_id,
        ];
    }
}
