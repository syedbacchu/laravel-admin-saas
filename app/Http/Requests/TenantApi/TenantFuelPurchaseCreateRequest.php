<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantFuelPurchaseCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $officeId = $this->normalizeNullableInteger('office_id');
        if ($officeId === null) {
            $officeId = $this->normalizeNullableInteger('branch_id');
        }

        $supplierId = $this->normalizeNullableInteger('supplier_id');
        if ($supplierId === null) {
            $supplierId = $this->normalizeNullableInteger('supplier_name');
        }

        $vehicleId = $this->normalizeNullableInteger('vehicle_id');
        if ($vehicleId === null) {
            $vehicleId = $this->normalizeNullableInteger('vehicle_no_id');
        }
        if ($vehicleId === null) {
            $vehicleId = $this->normalizeNullableInteger('vehicle_no');
        }

        $quantity = $this->normalizeNullableNumeric('quantity');
        $unitPrice = $this->normalizeNullableNumeric('unit_price');
        $total = $this->normalizeNullableNumeric('total');
        if ($total === null && $quantity !== null && $unitPrice !== null) {
            $total = ($quantity + 0) * ($unitPrice + 0);
        }

        $this->merge([
            'purchase_date' => $this->normalizeNullableString('purchase_date'),
            'office_id' => $officeId,
            'supplier_id' => $supplierId,
            'fuel_type' => trim((string) $this->input('fuel_type', '')),
            'vehicle_id' => $vehicleId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => $total,
            'bill_document' => $this->normalizeNullableString('bill_document') ?: $this->normalizeNullableString('bill_documents'),
            'trip_id' => $this->normalizeNullableInteger('trip_id'),
        ]);
    }

    public function rules(): array
    {
        return [
            'purchase_date' => ['required', 'date'],
            'office_id' => ['required', 'integer', 'exists:tenant.offices,id'],
            'supplier_id' => ['required', 'integer', 'exists:tenant.suppliers,id'],
            'fuel_type' => ['required', 'string', 'max:50'],
            'vehicle_id' => ['required', 'integer', 'exists:tenant.vehicles,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'total' => ['nullable', 'numeric', 'min:0'],
            'bill_document' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([0, 1])],
            'trip_id' => ['nullable', 'integer', 'exists:tenant.trips,id'],
        ];
    }

    protected function normalizeNullableString(string $key): ?string
    {
        $value = trim((string) $this->input($key, ''));
        return $value !== '' ? $value : null;
    }

    protected function normalizeNullableInteger(string $key): ?int
    {
        $value = $this->input($key, null);
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    protected function normalizeNullableNumeric(string $key): float|int|null
    {
        $value = $this->input($key, null);
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? $value + 0 : null;
    }
}

