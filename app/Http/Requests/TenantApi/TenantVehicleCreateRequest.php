<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantVehicleCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $registrationNo = strtoupper(trim((string) $this->input('registration_no', '')));

        if ($registrationNo === '') {
            $registrationNo = strtoupper(trim((string) $this->input('vehicle_name', '')));
        }

        $driverIds = $this->normalizeIdArray('driver_ids');
        $helperIds = $this->normalizeIdArray('helper_ids');
        $supervisorIds = $this->normalizeIdArray('supervisor_ids');

        $this->merge([
            'date' => $this->toNullableString('date'),
            'vehicle_name' => $this->toNullableString('vehicle_name'),
            'driver_ids' => $driverIds,
            'helper_ids' => $helperIds,
            'supervisor_ids' => $supervisorIds,
            'vehicle_category_id' => $this->toNullableInteger('vehicle_category_id'),
            'vehicle_size_id' => $this->toNullableInteger('vehicle_size_id'),
            'vehicle_kpl' => $this->toNullableNumeric('vehicle_kpl'),
            'fuel_capacity' => $this->toNullableNumeric('fuel_capacity'),
            'registration_serial_id' => $this->toNullableInteger('registration_serial_id'),
            'registration_zone_id' => $this->toNullableInteger('registration_zone_id'),
            'registration_expired_date' => $this->toNullableString('registration_expired_date'),
            'tax_expired_date' => $this->toNullableString('tax_expired_date'),
            'road_permit_expired_date' => $this->toNullableString('road_permit_expired_date'),
            'fitness_expired_date' => $this->toNullableString('fitness_expired_date'),
            'insurance_expired_date' => $this->toNullableString('insurance_expired_date'),
            'registration_no' => $registrationNo,
            'brand' => $this->toNullableString('brand'),
            'model' => $this->toNullableString('model'),
            'image' => $this->toNullableString('image'),
            'manufacturing_year' => $this->toNullableInteger('manufacturing_year'),
            'color' => $this->toNullableString('color'),
            'notes' => $this->toNullableString('notes'),
        ]);
    }

    public function rules(): array
    {
        $id = (int) ($this->route('vehicles') ?? $this->route('id') ?? $this->input('edit_id') ?? 0);

        return [
            'registration_no' => [
                'required',
                'string',
                'max:80',
                Rule::unique('tenant.vehicles', 'registration_no')->ignore($id),
            ],
            'date' => ['nullable', 'date'],
            'vehicle_name' => ['nullable', 'string', 'max:120'],
            'driver_ids' => ['nullable', 'array'],
            'driver_ids.*' => ['integer', 'distinct', 'exists:tenant.drivers,id'],
            'helper_ids' => ['nullable', 'array'],
            'helper_ids.*' => ['integer', 'distinct', 'exists:tenant.employees,id,employee_type,helper'],
            'supervisor_ids' => ['nullable', 'array'],
            'supervisor_ids.*' => ['integer', 'distinct', 'exists:tenant.employees,id,employee_type,supervisor'],
            'vehicle_category_id' => ['nullable', 'integer', 'exists:vehicle_categories,id'],
            'vehicle_size_id' => ['nullable', 'integer', 'exists:vehicle_category_sizes,id'],
            'vehicle_kpl' => ['nullable', 'numeric', 'min:0'],
            'fuel_capacity' => ['nullable', 'numeric', 'min:0'],
            'registration_serial_id' => ['nullable', 'integer', 'exists:registration_serials,id'],
            'registration_zone_id' => ['nullable', 'integer', 'exists:registration_zones,id'],
            'registration_expired_date' => ['nullable', 'date'],
            'tax_expired_date' => ['nullable', 'date'],
            'road_permit_expired_date' => ['nullable', 'date'],
            'fitness_expired_date' => ['nullable', 'date'],
            'insurance_expired_date' => ['nullable', 'date'],
            'brand' => ['nullable', 'string', 'max:80'],
            'model' => ['nullable', 'string', 'max:80'],
            'image' => ['nullable', 'string', 'max:255'],
            'manufacturing_year' => ['nullable', 'integer', 'between:1900,2100'],
            'color' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in([0, 1])],
        ];
    }

    protected function toNullableString(string $key): ?string
    {
        $value = trim((string) $this->input($key, ''));
        return $value !== '' ? $value : null;
    }

    protected function toNullableInteger(string $key): ?int
    {
        $raw = $this->input($key, null);
        if ($raw === null || $raw === '') {
            return null;
        }

        return (int) $raw;
    }

    protected function normalizeIdArray(string $key): array
    {
        $values = $this->input($key, []);
        if (!is_array($values)) {
            $values = [$values];
        }

        $normalized = [];
        foreach ($values as $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $normalized[] = (int) $value;
        }

        return array_values(array_unique($normalized));
    }

    protected function toNullableNumeric(string $key): float|int|null
    {
        $raw = $this->input($key, null);
        if ($raw === null || $raw === '') {
            return null;
        }

        return is_numeric($raw) ? $raw + 0 : null;
    }
}
