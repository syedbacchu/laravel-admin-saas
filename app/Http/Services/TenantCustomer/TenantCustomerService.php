<?php

namespace App\Http\Services\TenantCustomer;

use App\Http\Requests\TenantApi\TenantCustomerCreateRequest;
use App\Http\Services\BaseService;
use App\Models\Tenant;
use App\Models\Tenant\TenantCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class TenantCustomerService extends BaseService implements TenantCustomerServiceInterface
{
    protected TenantCustomerRepositoryInterface $tenantCustomerRepository;

    public function __construct(TenantCustomerRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->tenantCustomerRepository = $repository;
    }

    public function customerList(Request $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $data = $this->tenantCustomerRepository->customerList($request);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function storeCustomer(TenantCustomerCreateRequest $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        try {
            $addressItems = $request->validated('address') ?? [];
            $data = [
                'name' => (string) $request->name,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'image' => $request->image,
                'address' => $this->extractPrimaryAddress($addressItems),
                'rate_status' => $request->rate_status ?: 'fixed',
                'opening_balance' => $request->opening_balance ?? 0,
                'status' => (int) ($request->status ?? 1),
            ];

            if ($request->edit_id) {
                $item = $this->tenantCustomerRepository->findCustomer((int) $request->edit_id);
                if (!$item) {
                    return $this->sendResponse(false, __('Customer not found'), [], 404);
                }

                DB::transaction(function () use ($item, $data, $addressItems): void {
                    $this->tenantCustomerRepository->update((int) $item->id, $data);
                    $fresh = $this->tenantCustomerRepository->findCustomer((int) $item->id);

                    if ($fresh instanceof TenantCustomer) {
                        $this->syncCustomerAddresses($fresh, $addressItems);
                    }
                });

                $item = $this->tenantCustomerRepository->findCustomer((int) $item->id);

                return $this->sendResponse(true, __('Customer updated successfully'), $item);
            }

            $item = DB::transaction(function () use ($data, $addressItems) {
                $item = $this->tenantCustomerRepository->createCustomer($data);
                $fresh = $this->tenantCustomerRepository->findCustomer((int) $item->id);

                if ($fresh instanceof TenantCustomer) {
                    $this->syncCustomerAddresses($fresh, $addressItems);
                }

                return $item;
            });

            return $this->sendResponse(
                true,
                __('Customer created successfully'),
                $this->tenantCustomerRepository->findCustomer((int) $item->id)
            );
        } catch (Throwable $e) {
            logStore('TenantCustomerService storeCustomer', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function customerDetails(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantCustomerRepository->findCustomer($id);
        if (!$item) {
            return $this->sendResponse(false, __('Customer not found'), [], 404);
        }

        return $this->sendResponse(true, __('Data get successfully.'), $item);
    }

    public function deleteCustomer(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantCustomerRepository->findCustomer($id);
        if (!$item) {
            return $this->sendResponse(false, __('Customer not found'), [], 404);
        }

        $this->tenantCustomerRepository->delete($id);

        return $this->sendResponse(true, __('Customer deleted successfully'));
    }

    public function addCustomerAddress(Request $request, int $customerId): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $customer = $this->tenantCustomerRepository->findCustomer($customerId);
        if (!$customer) {
            return $this->sendResponse(false, __('Customer not found'), [], 404);
        }

        try {
            $address = $customer->addresses()->create([
                'name' => $request->input('name') ?: null,
                'address' => (string) $request->input('address'),
                'sort_order' => 0,
                'status' => (int) ($request->input('status') ?? 1),
            ]);

            // Return only the address data without relationships to avoid circular reference
            $addressData = [
                'id' => $address->id,
                'name' => $address->name,
                'address' => $address->address,
                'sort_order' => $address->sort_order,
                'status' => $address->status,
                'created_at' => $address->created_at,
                'updated_at' => $address->updated_at,
            ];

            return $this->sendResponse(true, __('Address added successfully'), $addressData);
        } catch (Throwable $e) {
            logStore('TenantCustomerService addCustomerAddress', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    protected function resolveTenantFromRequest(Request $request): ?Tenant
    {
        $tenant = $request->attributes->get('tenant');
        return $tenant instanceof Tenant ? $tenant : null;
    }

    protected function syncCustomerAddresses(TenantCustomer $customer, array $addressItems): void
    {
        $existingAddresses = $customer->addresses()->get()->keyBy('id');
        $processedIds = [];

        foreach (array_values($addressItems) as $index => $addressItem) {
            $addressId = isset($addressItem['id']) ? (int) $addressItem['id'] : 0;
            $payload = [
                'name' => $addressItem['name'] ?? null,
                'address' => (string) $addressItem['address'],
                'sort_order' => $index,
                'status' => (int) ($addressItem['status'] ?? 1),
            ];

            if ($addressId > 0 && $existingAddresses->has($addressId)) {
                $address = $existingAddresses->get($addressId);
                $address->update($payload);
                $processedIds[] = (int) $address->id;
                continue;
            }

            $created = $customer->addresses()->create($payload);
            $processedIds[] = (int) $created->id;
        }

        $customer->addresses()
            ->when($processedIds !== [], fn ($query) => $query->whereNotIn('id', $processedIds))
            ->when($processedIds === [], fn ($query) => $query)
            ->update(['status' => 0]);
    }

    protected function extractPrimaryAddress(array $addressItems): ?string
    {
        foreach ($addressItems as $addressItem) {
            $address = trim((string) ($addressItem['address'] ?? ''));
            if ($address !== '') {
                return $address;
            }
        }

        return null;
    }
}
