<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantOfficialProductPurchaseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'purchase_date' => $this->purchase_date,
            'category' => $this->category,
            'office_id' => $this->office_id,
            'office' => $this->office,
            'supplier_id' => $this->supplier_id,
            'supplier' => $this->supplier,
            'items' => $this->items,
            'service_charge' => $this->service_charge,
            'total_purchase_amount' => $this->total_purchase_amount,
            'remarks' => $this->remarks,
            'priority' => $this->priority,
            'bill_document' => $this->bill_document,
            'status' => (int) $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

