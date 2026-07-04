<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantOfficialProductPurchaseCreateRequest extends BaseFormRequest
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

        $this->merge([
            'purchase_date' => $this->normalizeNullableString('purchase_date'),
            'category' => trim((string) $this->input('category', '')),
            'office_id' => $officeId,
            'supplier_id' => $supplierId,
            'items' => $this->normalizeItems($this->input('items')),
            'service_charge' => $this->normalizeNullableNumeric('service_charge'),
            'total_purchase_amount' => $this->normalizeNullableNumeric('total_purchase_amount'),
            'remarks' => $this->normalizeNullableString('remarks'),
            'priority' => $this->normalizeNullableString('priority'),
            'bill_document' => $this->normalizeNullableString('bill_document') ?: $this->normalizeNullableString('bill_documents'),
        ]);
    }

    public function rules(): array
    {
        return [
            'purchase_date' => ['required', 'date'],
            'category' => ['required', 'string', 'max:120'], // it_product, electrical, stationary
            'office_id' => ['required', 'integer', 'exists:tenant.offices,id'],
            'supplier_id' => ['required', 'integer', 'exists:tenant.suppliers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:150'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.total' => ['nullable', 'numeric', 'min:0'],
            'service_charge' => ['nullable', 'numeric'],
            'total_purchase_amount' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', 'string', 'max:120'],
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
}

