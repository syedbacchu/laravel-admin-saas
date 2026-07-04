<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantMaintenancePurchaseResource extends JsonResource
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
            'vehicle_id' => $this->vehicle_id,
            'vehicle' => $this->vehicle,
            'driver_id' => $this->driver_id,
            'driver' => $this->driver,
            'category' => $this->category,
            'items' => $this->items,
            'service_charge' => $this->service_charge,
            'total_purchase_amount' => $this->total_purchase_amount,
            'service_date' => $this->service_date,
            'next_service_date' => $this->next_service_date,
            'document_renew_date' => $this->document_renew_date,
            'document_next_expire_date' => $this->document_next_expire_date,
            'remarks' => $this->remarks,
            'bill_document' => $this->bill_document,
            'status' => (int) $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
