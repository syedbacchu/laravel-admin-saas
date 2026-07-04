<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantFuelPurchaseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'purchase_date' => $this->purchase_date,
            'office_id' => $this->office_id,
            'office' => $this->office,
            'supplier_id' => $this->supplier_id,
            'supplier' => $this->supplier,
            'fuel_type' => $this->fuel_type,
            'vehicle_id' => $this->vehicle_id,
            'vehicle' => $this->vehicle,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total' => $this->total,
            'bill_document' => $this->bill_document,
            'status' => (int) $this->status,
            'trip_id' => $this->trip_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

