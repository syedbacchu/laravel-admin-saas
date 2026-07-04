<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantTripCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $editId = $this->resolveEditId();
        $transportType = $this->normalizeTransportType(
            $this->normalizeNullableStringByAliases(['transport_type'])
        );

        $vehicleNoId = $this->normalizeNullableIntegerByAliases(['vehicle_no_id', 'vehicle_no', 'vehicle']);
        $vehicleId = $this->normalizeNullableIntegerByAliases(['vehicle_id', 'own_vehicle_id']);
        $rentVehicleId = $this->normalizeNullableIntegerByAliases(['rent_vehicle_id', 'vendor_vehicle_id']);

        if ($transportType === 'own_transport' && $vehicleId === null) {
            $vehicleId = $vehicleNoId;
        }

        if ($transportType === 'vendor_transport' && $rentVehicleId === null) {
            $rentVehicleId = $vehicleNoId;
        }

        $driverId = $this->normalizeNullableIntegerByAliases(['driver_id']);
        $driverName = $this->normalizeNullableStringByAliases(['driver_name', 'driver']);

        if ($transportType === 'own_transport') {
            $driverName = null;
        }

        if ($transportType === 'vendor_transport') {
            $driverId = null;
        }

        $odometerStartKm = $this->normalizeNullableNumericByAliases(['odometer_start_km', 'odometer_start']);
        $odometerEndKm = $this->normalizeNullableNumericByAliases(['odometer_end_km', 'odometer_end']);
        $runningKm = $this->normalizeNullableNumericByAliases(['running_km', 'running']);
        if ($runningKm === null && $odometerStartKm !== null && $odometerEndKm !== null) {
            $runningKm = max(0, ($odometerEndKm + 0) - ($odometerStartKm + 0));
        }

        $totalRentBillAmount = $this->normalizeNullableNumericByAliases([
            'total_rent_bill_amount',
            'total_rent',
            'bill_amount',
            'total_bill_amount',
        ]);
        $rentAdvance = $this->normalizeNullableNumericByAliases(['rent_advance']);
        $totalDemurrage = $this->normalizeNullableNumericByAliases(['total_demurrage']);
        $demurrageTotalRent = $this->normalizeNullableNumericByAliases(['demurrage_total_rent', 'demurrage_plus_total_rent']);
        if ($demurrageTotalRent === null && $totalDemurrage !== null && $totalRentBillAmount !== null) {
            $demurrageTotalRent = ($totalDemurrage + 0) + ($totalRentBillAmount + 0);
        }

        $vendorTotalDemurrage = $this->normalizeNullableNumericByAliases(['vendor_total_demurrage']);
        $vendorRent = $this->normalizeNullableNumericByAliases(['vendor_rent']);
        $vendorRentDemurrageTotal = $this->normalizeNullableNumericByAliases([
            'vendor_rent_demurrage_total',
            'vendor_rent_plus_demurrage',
        ]);
        if ($vendorRentDemurrageTotal === null && $vendorRent !== null) {
            $vendorRentDemurrageTotal = ($vendorRent + 0) + ($vendorTotalDemurrage + 0);
        }

        $advance = $this->normalizeNullableNumericByAliases(['advance']);
        $dueAmount = $this->normalizeNullableNumericByAliases(['due_amount']);
        if ($dueAmount === null && $vendorRentDemurrageTotal !== null) {
            $dueAmount = max(0, ($vendorRentDemurrageTotal + 0) - ($advance + 0));
        }

        $driverCommissionPercent = $this->normalizeNullableNumericByAliases(['driver_commission_percent']);
        $driverCommissionAmount = $this->normalizeNullableNumericByAliases(['driver_commission_amount']);
        if ($driverCommissionAmount === null && $driverCommissionPercent !== null && $totalRentBillAmount !== null) {
            $driverCommissionAmount = (($totalRentBillAmount + 0) * ($driverCommissionPercent + 0)) / 100;
        }

        $fuelCost = $this->normalizeNullableNumericByAliases(['fuel_cost']);
        $labourCost = $this->normalizeNullableNumericByAliases(['labour_cost']);
        $tollCost = $this->normalizeNullableNumericByAliases(['toll_cost']);
        $ferryCost = $this->normalizeNullableNumericByAliases(['ferry_cost']);
        $policeCost = $this->normalizeNullableNumericByAliases(['police_cost']);
        $chadaCost = $this->normalizeNullableNumericByAliases(['chada_cost']);
        $parkingCost = $this->normalizeNullableNumericByAliases(['parking_cost']);
        $challanCost = $this->normalizeNullableNumericByAliases(['challan_cost']);
        $foodCost = $this->normalizeNullableNumericByAliases(['food_cost']);
        $othersCost = $this->normalizeNullableNumericByAliases(['others_cost']);
        $nightGuard = $this->normalizeNullableNumericByAliases(['night_guard']);
        $additionalLoadCost = $this->normalizeNullableNumericByAliases(['additional_load_cost']);
        $driverAdvance = $this->normalizeNullableNumericByAliases(['driver_advance']);

        $totalExpense = $this->normalizeNullableNumericByAliases(['total_expense']);
        if ($totalExpense === null) {
            $expenseValues = [
                $driverCommissionAmount,
                $fuelCost,
                $labourCost,
                $tollCost,
                $ferryCost,
                $policeCost,
                $chadaCost,
                $parkingCost,
                $challanCost,
                $foodCost,
                $othersCost,
                $nightGuard,
                $additionalLoadCost,
            ];

            $hasAtLeastOneExpense = false;
            $totalExpense = 0;
            foreach ($expenseValues as $expenseValue) {
                if ($expenseValue !== null) {
                    $hasAtLeastOneExpense = true;
                    $totalExpense += $expenseValue + 0;
                }
            }

            if (!$hasAtLeastOneExpense) {
                $totalExpense = null;
            }
        }

        $this->merge([
            'edit_id' => $editId,
            'date' => $this->normalizeNullableStringByAliases(['date', 'trip_date']),
            'customer_id' => $this->normalizeNullableIntegerByAliases(['customer_id', 'customer']),
            'office_id' => $this->normalizeNullableIntegerByAliases(['office_id', 'branch_id', 'branch', 'branch_name']),
            'load_area_id' => $this->normalizeNullableIntegerByAliases(['load_area_id', 'load_point_id', 'load_point']),
            'unload_area_id' => $this->normalizeNullableIntegerByAliases(['unload_area_id', 'unload_point_id', 'unload_point']),
            'trip_type' => $this->normalizeTripType($this->normalizeNullableStringByAliases(['trip_type'])),
            'additional_unload_point' => $this->normalizeNullableStringByAliases(['additional_unload_point']),
            'sender_name' => $this->normalizeNullableStringByAliases(['sender_name', 'sender']),
            'product_details' => $this->normalizeNullableStringByAliases(['product_details', 'product']),
            'transport_type' => $transportType,
            'vendor_id' => $this->normalizeNullableIntegerByAliases(['vendor_id', 'vendor_name', 'vendor']),
            'vehicle_id' => $vehicleId,
            'rent_vehicle_id' => $rentVehicleId,
            'driver_id' => $driverId,
            'driver_name' => $driverName,
            'helper_id' => $this->normalizeNullableIntegerByAliases(['helper_id', 'helper_name', 'helper']),
            'supervisor_id' => $this->normalizeNullableIntegerByAliases(['supervisor_id', 'supervisor_name', 'supervisor']),
            'vehicle_category_id' => $this->normalizeNullableIntegerByAliases(['vehicle_category_id', 'vehicle_category']),
            'vehicle_size_id' => $this->normalizeNullableIntegerByAliases(['vehicle_size_id', 'vehicle_size']),
            'challan_no' => $this->normalizeNullableStringByAliases(['challan_no', 'challan']),
            'total_rent_bill_amount' => $totalRentBillAmount,
            'rent_advance' => $rentAdvance,
            'odometer_start_km' => $odometerStartKm,
            'odometer_end_km' => $odometerEndKm,
            'running_km' => $runningKm,
            'vehicle_kpl' => $this->normalizeNullableNumericByAliases(['vehicle_kpl', 'vehicles_kpl']),
            'fuel_quantity_liter' => $this->normalizeNullableNumericByAliases(['fuel_quantity_liter', 'fuel_quantity']),
            'fuel_cost_per_liter' => $this->normalizeNullableNumericByAliases(['fuel_cost_per_liter']),
            'demurrage_days' => $this->normalizeNullableIntegerByAliases(['demurrage_days']),
            'total_demurrage' => $totalDemurrage,
            'demurrage_total_rent' => $demurrageTotalRent,
            'vendor_demurrage_days' => $this->normalizeNullableIntegerByAliases(['vendor_demurrage_days']),
            'vendor_total_demurrage' => $vendorTotalDemurrage,
            'vendor_rent' => $vendorRent,
            'vendor_rent_demurrage_total' => $vendorRentDemurrageTotal,
            'advance' => $advance,
            'due_amount' => $dueAmount,
            'driver_advance' => $driverAdvance,
            'driver_commission_percent' => $driverCommissionPercent,
            'driver_commission_amount' => $driverCommissionAmount,
            'fuel_cost' => $fuelCost,
            'labour_cost' => $labourCost,
            'toll_cost' => $tollCost,
            'ferry_cost' => $ferryCost,
            'police_cost' => $policeCost,
            'chada_cost' => $chadaCost,
            'parking_cost' => $parkingCost,
            'challan_cost' => $challanCost,
            'food_cost' => $foodCost,
            'others_cost' => $othersCost,
            'night_guard' => $nightGuard,
            'additional_load_cost' => $additionalLoadCost,
            'total_expense' => $totalExpense,
            'remarks' => $this->normalizeNullableStringByAliases(['remarks', 'note']),
        ]);
    }

    public function rules(): array
    {
        $isOwnTransport = (string) $this->input('transport_type') === 'own_transport';
        $isVendorTransport = (string) $this->input('transport_type') === 'vendor_transport';
        $editId = $this->resolveEditId();

        return [
            'date' => ['required', 'date'],
            'customer_id' => ['required', 'integer', 'exists:tenant.customers,id'],
            'office_id' => ['nullable', 'integer', 'exists:tenant.offices,id'],
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
            'trip_type' => ['required', Rule::in(['single', 'round'])], // single or round
            'additional_unload_point' => ['nullable', 'string', 'max:150'],
            'sender_name' => ['nullable', 'string', 'max:150'],
            'product_details' => ['nullable', 'string', 'max:1000'],
            'transport_type' => ['required', Rule::in(['own_transport', 'vendor_transport'])],
            'vendor_id' => [$isVendorTransport ? 'required' : 'nullable', 'integer', 'exists:tenant.vendors,id'],
            'vehicle_id' => [$isOwnTransport ? 'required' : 'nullable', 'integer', 'exists:tenant.vehicles,id'],
            'rent_vehicle_id' => [$isVendorTransport ? 'required' : 'nullable', 'integer', 'exists:tenant.rent_vehicles,id'],
            'driver_id' => [$isOwnTransport ? 'required' : 'nullable', 'integer', 'exists:tenant.drivers,id'],
            'driver_name' => [$isVendorTransport ? 'required' : 'nullable', 'string', 'max:150'],
            'helper_id' => ['nullable', 'integer', 'exists:tenant.employees,id,employee_type,helper'],
            'supervisor_id' => ['nullable', 'integer', 'exists:tenant.employees,id,employee_type,supervisor'],
            'vehicle_category_id' => [$isOwnTransport ? 'required' : 'nullable', 'integer', 'exists:vehicle_categories,id'],
            'vehicle_size_id' => [
                $isOwnTransport ? 'required' : 'nullable',
                'integer',
                Rule::exists('vehicle_category_sizes', 'id')->where(function ($query) {
                    $categoryId = (int) ($this->input('vehicle_category_id') ?? 0);
                    if ($categoryId > 0) {
                        $query->where('vehicle_category_id', $categoryId);
                    }
                }),
            ],
            'challan_no' => ['nullable', 'string', 'max:80', Rule::unique('tenant.trips', 'challan_no')->ignore($editId)],
            'total_rent_bill_amount' => ['nullable', 'numeric', 'min:0'],
            'rent_advance' => ['nullable', 'numeric', 'min:0'],
            'odometer_start_km' => [$isOwnTransport ? 'nullable' : 'exclude_if:transport_type,vendor_transport', 'numeric', 'min:0'],
            'odometer_end_km' => [$isOwnTransport ? 'nullable' : 'exclude_if:transport_type,vendor_transport', 'numeric', 'min:0', 'gt:odometer_start_km'],
            'running_km' => ['nullable', 'numeric', 'min:0'],
            'vehicle_kpl' => [$isOwnTransport ? 'nullable' : 'exclude_if:transport_type,vendor_transport', 'numeric', 'min:0'],
            'fuel_quantity_liter' => [$isOwnTransport ? 'nullable' : 'exclude_if:transport_type,vendor_transport', 'numeric', 'min:0'],
            'fuel_cost_per_liter' => [$isOwnTransport ? 'nullable' : 'exclude_if:transport_type,vendor_transport', 'numeric', 'min:0'],
            'demurrage_days' => ['nullable', 'integer', 'min:0'],
            'total_demurrage' => ['nullable', 'numeric', 'min:0'],
            'demurrage_total_rent' => ['nullable', 'numeric', 'min:0'],
            'vendor_demurrage_days' => ['nullable', 'integer', 'min:0'],
            'vendor_total_demurrage' => ['nullable', 'numeric', 'min:0'],
            'vendor_rent' => [$isVendorTransport ? 'required' : 'nullable', 'numeric', 'min:0'],
            'vendor_rent_demurrage_total' => ['nullable', 'numeric', 'min:0'],
            'advance' => ['nullable', 'numeric', 'min:0'],
            'due_amount' => ['nullable', 'numeric', 'min:0'],
            'driver_advance' => [$isOwnTransport ? 'nullable' : 'exclude_if:transport_type,vendor_transport', 'numeric', 'min:0'],
            'driver_commission_percent' => [$isOwnTransport ? 'nullable' : 'exclude_if:transport_type,vendor_transport', 'numeric', 'min:0', 'max:100'],
            'driver_commission_amount' => [$isOwnTransport ? 'nullable' : 'exclude_if:transport_type,vendor_transport', 'numeric', 'min:0'],
            'fuel_cost' => ['nullable', 'numeric', 'min:0'],
            'labour_cost' => ['nullable', 'numeric', 'min:0'],
            'toll_cost' => ['nullable', 'numeric', 'min:0'],
            'ferry_cost' => ['nullable', 'numeric', 'min:0'],
            'police_cost' => ['nullable', 'numeric', 'min:0'],
            'chada_cost' => ['nullable', 'numeric', 'min:0'],
            'parking_cost' => ['nullable', 'numeric', 'min:0'],
            'challan_cost' => ['nullable', 'numeric', 'min:0'],
            'food_cost' => ['nullable', 'numeric', 'min:0'],
            'others_cost' => ['nullable', 'numeric', 'min:0'],
            'night_guard' => ['nullable', 'numeric', 'min:0'],
            'additional_load_cost' => ['nullable', 'numeric', 'min:0'],
            'total_expense' => [$isOwnTransport ? 'nullable' : 'exclude_if:transport_type,vendor_transport', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', Rule::in([0, 1])],
        ];
    }

    protected function normalizeTransportType(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return null;
        }

        return match ($normalized) {
            'own', 'own_transport', 'own transport', '1' => 'own_transport',
            'vendor', 'vendor_transport', 'vendor transport', '2' => 'vendor_transport',
            default => str_replace(' ', '_', $normalized),
        };
    }

    protected function normalizeTripType(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return null;
        }

        return str_replace(' ', '_', $normalized);
    }

    protected function resolveEditId(): ?int
    {
        $routeId = $this->route('id');
        $inputEditId = $this->input('edit_id');

        foreach ([$inputEditId, $routeId] as $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (is_numeric($value) && (int) $value > 0) {
                return (int) $value;
            }
        }

        return null;
    }

    protected function normalizeNullableStringByAliases(array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = trim((string) $this->input($key, ''));
            if ($value !== '') {
                return $value;
            }
        }

        return null;
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

    protected function normalizeNullableNumericByAliases(array $keys): float|int|null
    {
        foreach ($keys as $key) {
            $value = $this->input($key, null);
            if ($value === null || $value === '') {
                continue;
            }

            return is_numeric($value) ? $value + 0 : null;
        }

        return null;
    }
}
