<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantRoutePricingCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_id' => $this->normalizeNullableIntegerByAliases(['customer_id', 'customer']),
            'vehicle_category_id' => $this->normalizeNullableIntegerByAliases(['vehicle_category_id', 'vehicle_category']),
            'load_area_id' => $this->normalizeNullableIntegerByAliases(['load_area_id', 'load_point_id', 'load_point']),
            'unload_area_id' => $this->normalizeNullableIntegerByAliases(['unload_area_id', 'unload_point_id', 'unload_point']),
            'vehicle_size_id' => $this->normalizeNullableIntegerByAliases(['vehicle_size_id', 'vehicle_size']),
            'rate' => $this->normalizeNullableNumeric('rate'),
        ]);
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:tenant.customers,id'],
            'vehicle_category_id' => ['required', 'integer', 'exists:vehicle_categories,id'],
            'load_area_id' => [
                'required',
                'integer',
                Rule::exists('tenant.customer_addresses', 'id')->where(function ($query) {
                    $query
                        ->where('customer_id', (int) ($this->input('customer_id') ?? 0))
                        ->where('status', 1);
                }),
            ],
            'unload_area_id' => ['required', 'integer', 'exists:areas,id'],
            'vehicle_size_id' => [
                'required',
                'integer',
                Rule::exists('vehicle_category_sizes', 'id')->where(function ($query) {
                    $query->where('vehicle_category_id', (int) $this->input('vehicle_category_id'));
                }),
            ],
            'rate' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', Rule::in([0, 1])],
        ];
    }

    protected function normalizeNullableIntegerByAliases(array $keys): ?int
    {
        foreach ($keys as $key) {
            $value = $this->input($key, null);
            if ($value === null || $value === '') {
                continue;
            }

            return (int) $value;
        }

        return null;
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
