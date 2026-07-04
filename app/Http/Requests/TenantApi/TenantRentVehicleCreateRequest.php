<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantRentVehicleCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $vendorId = $this->normalizeNullableInteger('vendor_id');
        $driverName = trim((string) $this->input('driver_name', $this->input('vendor_driver_name', '')));

        $partyType = strtolower(trim((string) ($this->input('vendor_driver_type') ?? $this->input('party_type') ?? '')));
        $partyId = $this->input('vendor_driver_id', $this->input('party_id'));
        if (($partyId !== null && $partyId !== '') && is_numeric($partyId)) {
            if ($partyType === 'vendor') {
                $vendorId = (int) $partyId;
            }
        }

        if ($partyType === 'driver') {
            $driverName = trim((string) $this->input('driver_name', $this->input('vendor_driver_name', $this->input('party_name', ''))));
        }

        $this->merge([
            'vehicle_name' => trim((string) $this->input('vehicle_name', '')),
            'vendor_id' => $vendorId,
            'driver_name' => $driverName !== '' ? $driverName : null,
            'vehicle_category_id' => $this->normalizeNullableInteger('vehicle_category_id'),
            'vehicle_size_id' => $this->normalizeNullableInteger('vehicle_size_id'),
            'registration_number' => strtoupper(trim((string) $this->input('registration_number', ''))),
            'registration_serial_id' => $this->normalizeNullableInteger('registration_serial_id'),
            'registration_zone_id' => $this->normalizeNullableInteger('registration_zone_id'),
        ]);
    }

    public function rules(): array
    {
        return [
            'vehicle_name' => ['required', 'string', 'max:150'],
            'vendor_id' => ['nullable', 'integer', 'exists:tenant.vendors,id', 'required_without:driver_name'],
            'driver_name' => ['nullable', 'string', 'max:150', 'required_without:vendor_id'],
            'vehicle_category_id' => ['required', 'integer', 'exists:vehicle_categories,id'],
            'vehicle_size_id' => ['required', 'integer', 'exists:vehicle_category_sizes,id'],
            'registration_number' => ['required', 'string', 'max:80'],
            'registration_serial_id' => ['required', 'integer', 'exists:registration_serials,id'],
            'registration_zone_id' => ['required', 'integer', 'exists:registration_zones,id'],
            'status' => ['nullable', Rule::in([0, 1])],
        ];
    }

    protected function normalizeNullableInteger(string $key): ?int
    {
        $value = $this->input($key, null);
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
