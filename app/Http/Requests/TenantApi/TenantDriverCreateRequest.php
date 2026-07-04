<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use App\Rules\PhoneNumberBD;
use App\Rules\UserName;
use Illuminate\Validation\Rule;

class TenantDriverCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name = trim((string) $this->input('name', $this->input('driver_name', '')));
        $phone = trim((string) $this->input('phone', $this->input('mobile', $this->input('driver_mobile', ''))));
        $licenseNo = strtoupper(trim((string) $this->input('license_no', '')));
        $nidNo = trim((string) $this->input('nid_no', $this->input('nid_number', $this->input('nid', ''))));
        $address = trim((string) $this->input('address', ''));
        $emergencyContact = trim((string) $this->input('emergency_contact', ''));
        $notes = trim((string) $this->input('notes', $this->input('note', '')));
        $loginPhone = trim((string) $this->input('login_phone', ''));

        $this->merge([
            'name' => $name,
            'phone' => $phone !== '' ? $phone : null,
            'mobile' => $phone !== '' ? $phone : null,
            'license_no' => $licenseNo !== '' ? $licenseNo : null,
            'nid_no' => $nidNo !== '' ? $nidNo : null,
            'image' => $this->normalizeNullableString('image'),
            'address' => $address !== '' ? $address : null,
            'emergency_contact' => $emergencyContact !== '' ? $emergencyContact : null,
            'notes' => $notes !== '' ? $notes : null,
            'license_expired_date' => $this->normalizeNullableString('license_expired_date'),
            'vehicle_category_id' => $this->normalizeNullableInteger('vehicle_category_id'),
            'opening_balance' => $this->normalizeNullableNumeric('opening_balance'),
            'login_phone' => $loginPhone !== '' ? $loginPhone : null,
            'login_email' => $this->input('login_email') ? strtolower(trim((string) $this->input('login_email'))) : null,
            'login_username' => $this->input('login_username') ? strtolower(trim((string) $this->input('login_username'))) : null,
        ]);
    }

    public function rules(): array
    {
        $id = (int) ($this->route('driver') ?? $this->route('id') ?? $this->input('edit_id') ?? 0);

        return [
            'vehicle_category_id' => ['required', 'integer', 'exists:vehicle_categories,id'],
            'name' => ['required', 'string', 'max:120'],
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('tenant.drivers', 'phone')->ignore($id),
            ],
            'emergency_contact' => ['nullable', 'string', 'max:30'],
            'license_no' => [
                'required',
                'string',
                'max:80',
                Rule::unique('tenant.drivers', 'license_no')->ignore($id),
            ],
            'license_expired_date' => ['required', 'date'],
            'nid_no' => [
                'required',
                'string',
                'max:40',
                Rule::unique('tenant.drivers', 'nid_no')->ignore($id),
            ],
            'image' => ['nullable', 'string', 'max:255'],
            'joining_date' => ['nullable', 'date'],
            'address' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'opening_balance' => ['nullable', 'numeric'],
            'status' => ['nullable', Rule::in([0, 1])],
            'login_name' => ['nullable', 'string', 'max:255'],
            'login_email' => ['nullable', 'email', 'max:255'],
            'login_phone' => ['nullable', new PhoneNumberBD()],
            'login_username' => ['nullable', new UserName()],
            'login_password' => ['nullable', 'string', 'min:8'],
            'login_enable_login' => ['nullable', Rule::in([0, 1])],
            'login_status' => ['nullable', Rule::in([0, 1])],
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
