<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantCustomerCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $address = $this->normalizeAddressInput($this->input('address'));

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'mobile' => $this->input('mobile') ? trim((string) $this->input('mobile')) : null,
            'email' => $this->input('email') ? strtolower(trim((string) $this->input('email'))) : null,
            'image' => $this->input('image') ? trim((string) $this->input('image')) : null,
            'address' => $address,
            'rate_status' => $this->input('rate_status') ? strtolower(trim((string) $this->input('rate_status'))) : 'fixed',
        ]);
    }

    public function rules(): array
    {
        $id = (int) ($this->route('customer') ?? $this->route('id') ?? $this->input('edit_id') ?? 0);

        return [
            'name' => ['required', 'string', 'max:150'],
            'mobile' => ['required', 'string', 'max:30', Rule::unique('tenant.customers', 'mobile')->ignore($id)],
            'email' => ['nullable', 'email', 'max:180'],
            'image' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'array'],
            'address.*' => ['array'],
            'address.*.id' => ['nullable', 'integer'],
            'address.*.name' => ['nullable', 'string', 'max:150'],
            'address.*.address' => ['required', 'string', 'max:500'],
            'address.*.status' => ['nullable', Rule::in([0, 1])],
            'rate_status' => ['nullable', 'string', 'max:30'],
            'opening_balance' => ['nullable', 'numeric'],
            'status' => ['nullable', Rule::in([0, 1])],
        ];
    }

    protected function normalizeAddressInput(mixed $address): ?array
    {
        if ($address === null) {
            return null;
        }

        if (is_string($address)) {
            $address = trim($address);
            if ($address === '') {
                return null;
            }

            $decoded = json_decode($address, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $address = $decoded;
            } else {
                $address = [['name' => null, 'address' => $address]];
            }
        }

        if (!is_array($address)) {
            return null;
        }

        if (array_key_exists('address', $address) || array_key_exists('name', $address)) {
            $address = [$address];
        }

        $normalized = [];

        foreach ($address as $item) {
            if (is_string($item)) {
                $item = ['name' => null, 'address' => trim($item)];
            }

            if (!is_array($item)) {
                continue;
            }

            $name = isset($item['name']) ? trim((string) $item['name']) : null;
            $line = isset($item['address']) ? trim((string) $item['address']) : null;
            $id = isset($item['id']) && $item['id'] !== '' ? (int) $item['id'] : null;
            $status = isset($item['status']) ? (int) $item['status'] : 1;

            if ($name === '') {
                $name = null;
            }

            if ($line === null || $line === '') {
                continue;
            }

            $normalized[] = [
                'id' => $id,
                'name' => $name,
                'address' => $line,
                'status' => $status === 0 ? 0 : 1,
            ];
        }

        return $normalized === [] ? null : array_values($normalized);
    }
}
