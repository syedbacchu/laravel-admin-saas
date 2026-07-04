<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantMaintenancePurchaseCreateRequest extends BaseFormRequest
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

        $driverId = $this->normalizeNullableInteger('driver_id');
        $category = $this->normalizeCategory(trim((string) $this->input('category', '')));

        $this->merge([
            'purchase_date' => $this->normalizeNullableString('purchase_date'),
            'office_id' => $officeId,
            'supplier_id' => $supplierId,
            'vehicle_id' => $vehicleId,
            'driver_id' => $driverId,
            'category' => $category,
            'items' => $this->normalizeItems($this->input('items')),
            'service_charge' => $this->normalizeNullableNumeric('service_charge'),
            'total_purchase_amount' => $this->normalizeNullableNumeric('total_purchase_amount'),
            'service_date' => $this->normalizeNullableString('service_date'),
            'next_service_date' => $this->normalizeNullableString('next_service_date'),
            'document_renew_date' => $this->normalizeNullableString('document_renew_date') ?: $this->normalizeNullableString('renew_date'),
            'document_next_expire_date' => $this->normalizeNullableString('document_next_expire_date') ?: $this->normalizeNullableString('expire_date'),
            'remarks' => $this->normalizeNullableString('remarks'),
            'bill_document' => $this->normalizeNullableString('bill_document') ?: $this->normalizeNullableString('bill_documents'),
        ]);
    }

    public function rules(): array
    {
        $isDocuments = (string) $this->input('category') === 'documents';

        return [
            'purchase_date' => ['required', 'date'],
            'office_id' => ['required', 'integer', 'exists:tenant.offices,id'],
            'supplier_id' => ['required', 'integer', 'exists:tenant.suppliers,id'],
            'vehicle_id' => ['required', 'integer', 'exists:tenant.vehicles,id'],
            'driver_id' => ['nullable', 'integer', 'exists:tenant.drivers,id'],
            'category' => ['required', Rule::in(['engine_oil', 'parts', 'documents'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:150'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.total' => ['nullable', 'numeric', 'min:0'],
            'service_charge' => ['nullable', 'numeric'],
            'total_purchase_amount' => ['required', 'numeric', 'min:0'],
            'service_date' => ['nullable', 'date'],
            'next_service_date' => ['nullable', 'date', 'after_or_equal:service_date'],
            'document_renew_date' => [$isDocuments ? 'required' : 'nullable', 'date'],
            'document_next_expire_date' => [$isDocuments ? 'required' : 'nullable', 'date', 'after_or_equal:document_renew_date'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'bill_document' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([0, 1])],
        ];
    }

    protected function normalizeItems(mixed $items): array
    {
        if (is_string($items)) {
            $decoded = json_decode($items, true);
            $items = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($items)) {
            return [];
        }

        $normalized = [];
        foreach ($items as $row) {
            if (!is_array($row)) {
                continue;
            }

            $itemName = trim((string) ($row['item_name'] ?? $row['name'] ?? ''));
            $quantity = $row['quantity'] ?? $row['qty'] ?? null;
            $unitPrice = $row['unit_price'] ?? $row['price'] ?? null;
            $total = $row['total'] ?? null;

            $quantity = is_numeric($quantity) ? $quantity + 0 : null;
            $unitPrice = is_numeric($unitPrice) ? $unitPrice + 0 : null;
            $total = is_numeric($total) ? $total + 0 : null;

            if ($total === null && $quantity !== null && $unitPrice !== null) {
                $total = $quantity * $unitPrice;
            }

            $normalized[] = [
                'item_name' => $itemName,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $total,
            ];
        }

        return $normalized;
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

    protected function normalizeCategory(string $category): string
    {
        $normalized = strtolower(trim($category));
        return str_replace(' ', '_', $normalized);
    }
}
