<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;

class TenantTripBillSubmitRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $tripIds = $this->input('trip_ids', $this->input('trip_id', []));

        if (!is_array($tripIds)) {
            $tripIds = [$tripIds];
        }

        $normalizedTripIds = [];
        foreach ($tripIds as $tripId) {
            if ($tripId === null || $tripId === '') {
                continue;
            }

            $normalizedTripIds[] = (int) $tripId;
        }

        $this->merge([
            'trip_ids' => array_values(array_unique($normalizedTripIds)),
        ]);
    }

    public function rules(): array
    {
        return [
            'trip_ids' => ['required', 'array', 'min:1'],
            'trip_ids.*' => ['required', 'integer', 'distinct', 'exists:tenant.trips,id'],
        ];
    }
}
