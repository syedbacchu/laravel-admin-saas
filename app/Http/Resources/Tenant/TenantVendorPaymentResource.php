<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantVendorPaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'vendor_id' => $this->vendor_id,
            'vendor' => $this->vendor,
            'office_id' => $this->office_id,
            'branch_name' => $this->office['branch_name'] ?? null,
            'office' => $this->office,
            'bill_ref' => $this->bill_ref,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'note' => $this->note,
            'bill_document' => $this->bill_document,
            'status' => (int) $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
