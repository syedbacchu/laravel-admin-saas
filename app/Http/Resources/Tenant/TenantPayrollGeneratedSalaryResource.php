<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantPayrollGeneratedSalaryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'added_by' => $this->added_by,
            'updated_by' => $this->updated_by,
            'generate_date' => $this->generate_date,
            'month' => $this->month,
            'generated_by' => $this->generated_by,
            'generated_by_user' => $this->generated_by_user,
            'created_by_user' => $this->created_by_user,
            'status' => (int) $this->status,
            'salary_sheet' => isset($this->salary_sheet) && is_iterable($this->salary_sheet)
                ? TenantPayrollSalarySheetResource::collection($this->salary_sheet)
                : [],
            'summary' => $this->summary ?? null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
