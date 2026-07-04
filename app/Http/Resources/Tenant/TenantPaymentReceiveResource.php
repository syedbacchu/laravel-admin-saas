<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantPaymentReceiveResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'customer_id' => $this->customer_id,
            'customer' => $this->customer,
            'office_id' => $this->office_id,
            'branch_name' => $this->office['branch_name'] ?? null,
            'office' => $this->office,
            'bill_ref' => $this->bill_ref,
            'amount' => $this->amount,
            'cash_type' => $this->cash_type,
            'note' => $this->note,
            'created_by' => $this->created_by,
            'bill_document' => $this->bill_document,
            'status' => (int) $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
