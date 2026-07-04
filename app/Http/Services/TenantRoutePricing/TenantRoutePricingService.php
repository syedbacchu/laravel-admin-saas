<?php

namespace App\Http\Services\TenantRoutePricing;

use App\Http\Requests\TenantApi\TenantRoutePricingCreateRequest;
use App\Http\Services\BaseService;
use App\Models\Area;
use App\Models\Tenant;
use App\Models\TenantCustomer;
use App\Models\TenantCustomerAddress;
use App\Models\TenantRoutePricing;
use App\Models\VehicleCategory;
use App\Models\VehicleCategorySize;
use App\Services\DistanceCalculationService;
use App\Support\LanguageResolver;
use App\Support\ModelTranslationResolver;
use Illuminate\Http\Request;
use Throwable;

class TenantRoutePricingService extends BaseService implements TenantRoutePricingServiceInterface
{
    protected TenantRoutePricingRepositoryInterface $tenantRoutePricingRepository;

    public function __construct(TenantRoutePricingRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->tenantRoutePricingRepository = $repository;
    }

    public function routePricingList(Request $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        [$languageId] = $this->resolveLanguageContext($request);
        $data = $this->tenantRoutePricingRepository->routePricingList($request);
        $this->attachReferenceDataToRoutePricingList($data, $languageId);

        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function storeRoutePricing(TenantRoutePricingCreateRequest $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        try {
            [$languageId] = $this->resolveLanguageContext($request);
            $loadAreaSelection = $this->resolveCustomerLoadAreaSelection(
                (int) $request->customer_id,
                (int) $request->load_area_id
            );

            if ($loadAreaSelection === null) {
                return $this->sendResponse(false, __('Selected customer load area not found'), [], 422);
            }

            $data = [
                'customer_id' => (int) $request->customer_id,
                'vehicle_category_id' => (int) $request->vehicle_category_id,
                'load_area_id' => (int) $request->load_area_id,
                'load_area_name' => $loadAreaSelection['name'],
                'load_area_address' => $loadAreaSelection['address'],
                'unload_area_id' => (int) $request->unload_area_id,
                'vehicle_size_id' => (int) $request->vehicle_size_id,
                'rate' => $request->rate,
                'distance' => $this->toNullableNumeric($request->distance),
                'status' => (int) ($request->status ?? 1),
            ];

            if ($request->edit_id) {
                $item = $this->tenantRoutePricingRepository->findRoutePricing((int) $request->edit_id);
                if (!$item) {
                    return $this->sendResponse(false, __('Route pricing not found'), [], 404);
                }

                $this->tenantRoutePricingRepository->update((int) $item->id, $data);
                $item = $this->tenantRoutePricingRepository->findRoutePricing((int) $item->id);
                if (!$item) {
                    return $this->sendResponse(false, __('Route pricing not found'), [], 404);
                }
                $this->attachReferenceDataToRoutePricing($item, $languageId);

                return $this->sendResponse(true, __('Route pricing updated successfully'), $item);
            }

            $item = $this->tenantRoutePricingRepository->createRoutePricing($data);
            $item = $this->tenantRoutePricingRepository->findRoutePricing((int) $item->id);
            if (!$item) {
                return $this->sendResponse(false, __('Route pricing not found'), [], 404);
            }
            $this->attachReferenceDataToRoutePricing($item, $languageId);

            return $this->sendResponse(true, __('Route pricing created successfully'), $item);
        } catch (Throwable $e) {
            logStore('TenantRoutePricingService storeRoutePricing', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function routePricingDetails(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantRoutePricingRepository->findRoutePricing($id);
        if (!$item) {
            return $this->sendResponse(false, __('Route pricing not found'), [], 404);
        }

        [$languageId] = $this->resolveLanguageContext($request);
        $this->attachReferenceDataToRoutePricing($item, $languageId);

        return $this->sendResponse(true, __('Data get successfully.'), $item);
    }

    public function deleteRoutePricing(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantRoutePricingRepository->findRoutePricing($id);
        if (!$item) {
            return $this->sendResponse(false, __('Route pricing not found'), [], 404);
        }

        $this->tenantRoutePricingRepository->delete($id);

        return $this->sendResponse(true, __('Route pricing deleted successfully'));
    }

    protected function resolveTenantFromRequest(Request $request): ?Tenant
    {
        $tenant = $request->attributes->get('tenant');
        return $tenant instanceof Tenant ? $tenant : null;
    }

    protected function resolveLanguageContext(Request $request): array
    {
        $language = LanguageResolver::resolveFromRequest($request, 'lang', 'en');
        $languageId = (int) ($language['id'] ?? 0);
        $languageCode = (string) ($language['code'] ?? 'en');

        return [$languageId, $languageCode];
    }

    protected function attachReferenceDataToRoutePricingList(array &$data, int $languageId): void
    {
        if (!isset($data['data']) || !is_iterable($data['data'])) {
            return;
        }

        $customerIds = [];
        $loadAreaIds = [];
        $categoryIds = [];
        $areaIds = [];
        $sizeIds = [];

        foreach ($data['data'] as $item) {
            $customerIds[] = (int) $item->customer_id;
            $loadAreaIds[] = (int) $item->load_area_id;
            $categoryIds[] = (int) $item->vehicle_category_id;
            $areaIds[] = (int) $item->unload_area_id;
            $sizeIds[] = (int) $item->vehicle_size_id;
        }

        $customerMap = $this->resolveCustomerMap($customerIds);
        $loadAreaMap = $this->resolveCustomerAddressMap($loadAreaIds);
        $categoryMap = $this->resolveCategoryMap($categoryIds, $languageId);
        $areaMap = $this->resolveAreaMap($areaIds, $languageId);
        $sizeMap = $this->resolveSizeMap($sizeIds, $languageId);

        foreach ($data['data'] as $item) {
            $this->applyReferenceMapsToRoutePricing($item, $customerMap, $loadAreaMap, $categoryMap, $areaMap, $sizeMap);
        }
    }

    protected function attachReferenceDataToRoutePricing(TenantRoutePricing $item, int $languageId): void
    {
        $customerMap = $this->resolveCustomerMap([(int) $item->customer_id]);
        $loadAreaMap = $this->resolveCustomerAddressMap([(int) $item->load_area_id]);
        $categoryMap = $this->resolveCategoryMap([(int) $item->vehicle_category_id], $languageId);
        $areaMap = $this->resolveAreaMap([(int) $item->unload_area_id], $languageId);
        $sizeMap = $this->resolveSizeMap([(int) $item->vehicle_size_id], $languageId);

        $this->applyReferenceMapsToRoutePricing($item, $customerMap, $loadAreaMap, $categoryMap, $areaMap, $sizeMap);
    }

    protected function applyReferenceMapsToRoutePricing(
        TenantRoutePricing $item,
        array $customerMap,
        array $loadAreaMap,
        array $categoryMap,
        array $areaMap,
        array $sizeMap
    ): void
    {
        $customerId = (int) $item->customer_id;
        $loadAreaId = (int) $item->load_area_id;
        $categoryId = (int) $item->vehicle_category_id;
        $unloadAreaId = (int) $item->unload_area_id;
        $sizeId = (int) $item->vehicle_size_id;
        $loadArea = $loadAreaMap[$loadAreaId] ?? $this->buildLoadAreaSnapshot($item);

        $item->setAttribute('customer', $customerMap[$customerId] ?? null);
        $item->setAttribute('vehicle_category', $categoryMap[$categoryId] ?? null);
        $item->setAttribute('load_area', $loadArea);
        $item->setAttribute('unload_area', $areaMap[$unloadAreaId] ?? null);
        $item->setAttribute('vehicle_size', $sizeMap[$sizeId] ?? null);
    }

    protected function resolveCustomerMap(array $customerIds): array
    {
        $customerIds = array_values(array_unique(array_filter(array_map('intval', $customerIds))));
        if (empty($customerIds)) {
            return [];
        }

        $customers = TenantCustomer::query()
            ->whereIn('id', $customerIds)
            ->get(['id', 'name', 'mobile']);

        $map = [];
        foreach ($customers as $customer) {
            $map[(int) $customer->id] = [
                'id' => (int) $customer->id,
                'name' => (string) $customer->name,
                'mobile' => $customer->mobile,
            ];
        }

        return $map;
    }

    protected function resolveCustomerAddressMap(array $addressIds): array
    {
        $addressIds = array_values(array_unique(array_filter(array_map('intval', $addressIds))));
        if (empty($addressIds)) {
            return [];
        }

        $addresses = TenantCustomerAddress::query()
            ->whereIn('id', $addressIds)
            ->get(['id', 'customer_id', 'name', 'address', 'status']);

        $map = [];
        foreach ($addresses as $address) {
            $map[(int) $address->id] = [
                'id' => (int) $address->id,
                'customer_id' => (int) $address->customer_id,
                'name' => $address->name,
                'address' => $address->address,
                'status' => (int) $address->status,
            ];
        }

        return $map;
    }

    protected function resolveCategoryMap(array $categoryIds, int $languageId): array
    {
        $categoryIds = array_values(array_unique(array_filter(array_map('intval', $categoryIds))));
        if (empty($categoryIds)) {
            return [];
        }

        $categories = VehicleCategory::query()
            ->whereIn('id', $categoryIds)
            ->with('translations:id,vehicle_category_id,language_id,name')
            ->get(['id', 'name']);

        $map = [];
        foreach ($categories as $category) {
            $map[(int) $category->id] = [
                'id' => (int) $category->id,
                'name' => (string) ModelTranslationResolver::getValue(
                    $category,
                    'translations',
                    $languageId,
                    'name',
                    'name',
                    ''
                ),
            ];
        }

        return $map;
    }

    protected function resolveSizeMap(array $sizeIds, int $languageId): array
    {
        $sizeIds = array_values(array_unique(array_filter(array_map('intval', $sizeIds))));
        if (empty($sizeIds)) {
            return [];
        }

        $sizes = VehicleCategorySize::query()
            ->whereIn('id', $sizeIds)
            ->with('translations:id,vehicle_category_size_id,language_id,name')
            ->get(['id', 'vehicle_category_id', 'name']);

        $map = [];
        foreach ($sizes as $size) {
            $map[(int) $size->id] = [
                'id' => (int) $size->id,
                'vehicle_category_id' => (int) $size->vehicle_category_id,
                'name' => (string) ModelTranslationResolver::getValue(
                    $size,
                    'translations',
                    $languageId,
                    'name',
                    'name',
                    ''
                ),
            ];
        }

        return $map;
    }

    protected function resolveAreaMap(array $areaIds, int $languageId): array
    {
        $areaIds = array_values(array_unique(array_filter(array_map('intval', $areaIds))));
        if (empty($areaIds)) {
            return [];
        }

        $areas = Area::query()
            ->whereIn('id', $areaIds)
            ->with('translations:id,area_id,language_id,name')
            ->get(['id', 'name']);

        $map = [];
        foreach ($areas as $area) {
            $map[(int) $area->id] = [
                'id' => (int) $area->id,
                'name' => (string) ModelTranslationResolver::getValue(
                    $area,
                    'translations',
                    $languageId,
                    'name',
                    'name',
                    ''
                ),
            ];
        }

        return $map;
    }

    protected function resolveCustomerLoadAreaSelection(int $customerId, int $loadAreaId): ?array
    {
        if ($customerId <= 0 || $loadAreaId <= 0) {
            return null;
        }

        $selected = TenantCustomerAddress::query()
            ->where('id', $loadAreaId)
            ->where('customer_id', $customerId)
            ->where('status', 1)
            ->first(['id', 'customer_id', 'name', 'address', 'status']);

        if (!$selected) {
            return null;
        }

        return [
            'id' => (int) $selected->id,
            'customer_id' => (int) $selected->customer_id,
            'name' => $selected->name,
            'address' => $selected->address,
            'status' => (int) $selected->status,
        ];
    }

    protected function buildLoadAreaSnapshot(TenantRoutePricing $item): ?array
    {
        if (!$item->load_area_name && !$item->load_area_address) {
            return null;
        }

        return [
            'id' => $item->load_area_id === null ? null : (int) $item->load_area_id,
            'name' => $item->load_area_name,
            'address' => $item->load_area_address,
        ];
    }

    protected function toNullableNumeric(mixed $value): float|int|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? $value + 0 : null;
    }

    public function getDistanceByRoute(Request $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        try {
            $loadAreaId = (int) $request->input('load_area_id');
            $unloadAreaId = (int) $request->input('unload_area_id');

            if ($loadAreaId <= 0 || $unloadAreaId <= 0) {
                return $this->sendResponse(false, __('Load area and unload area are required'), [], 422);
            }

            $routePricing = TenantRoutePricing::query()
                ->where('load_area_id', $loadAreaId)
                ->where('unload_area_id', $unloadAreaId)
                ->where('status', 1)
                ->first();

            if (!$routePricing) {
                return $this->sendResponse(false, __('Route not found'), [], 404);
            }

            return $this->sendResponse(true, __('Distance retrieved successfully'), [
                'distance' => (float) ($routePricing->distance ?? 0),
            ]);
        } catch (Throwable $e) {
            logStore('TenantRoutePricingService getDistanceByRoute', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function calculateDistanceByGoogleMaps(Request $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        try {
            $loadAreaId = (int) $request->input('load_area_id');
            $unloadAreaId = (int) $request->input('unload_area_id');

            \Log::info('[TenantRoutePricingService] calculateDistanceByGoogleMaps called', [
                'load_area_id' => $loadAreaId,
                'unload_area_id' => $unloadAreaId,
            ]);

            if ($loadAreaId <= 0 || $unloadAreaId <= 0) {
                return $this->sendResponse(false, __('Load area and unload area are required'), [], 422);
            }

            // Get area addresses
            $loadArea = TenantCustomerAddress::query()->find($loadAreaId);
            $unloadArea = Area::query()->find($unloadAreaId);

            if (!$loadArea || !$unloadArea) {
                \Log::warning('[TenantRoutePricingService] Areas not found', [
                    'load_area_found' => $loadArea !== null,
                    'unload_area_found' => $unloadArea !== null,
                ]);
                return $this->sendResponse(false, __('Areas not found'), [], 404);
            }

            // Build origin and destination addresses
            $origin = trim(($loadArea->address ?? '') . ' ' . ($loadArea->name ?? ''));
            $destination = $unloadArea->name ?? '';

            \Log::info('[TenantRoutePricingService] Addresses prepared', [
                'origin' => $origin,
                'destination' => $destination,
            ]);

            if (empty($origin) || empty($destination)) {
                return $this->sendResponse(false, __('Area addresses are required for distance calculation'), [], 422);
            }

            // STEP 1: Try to calculate distance using the configured provider (Google Maps or Barikoi)
            $distanceCalculationService = new DistanceCalculationService(
                new \App\Services\GoogleMapsService(),
                new \App\Services\BarikoiService()
            );

            $providerName = $distanceCalculationService->getProviderName();
            \Log::info('[TenantRoutePricingService] Step 1: Using map provider', [
                'provider' => $providerName,
            ]);

            $result = $distanceCalculationService->calculateDistanceWithCache($origin, $destination);

            if ($result) {
                \Log::info('[TenantRoutePricingService] Distance calculated successfully via provider', [
                    'distance_km' => $result['distance_km'],
                    'provider' => $providerName,
                ]);

                return $this->sendResponse(true, __('Distance calculated successfully'), [
                    'distance' => round($result['distance_km'], 2),
                    'distance_text' => $result['distance_text'],
                    'duration_text' => $result['duration_text'] ?? null,
                    'origin' => $origin,
                    'destination' => $destination,
                    'provider' => $providerName,
                    'source' => 'provider',
                ]);
            }

            // STEP 2: Provider failed, try to get distance from existing route pricing
            \Log::warning('[TenantRoutePricingService] Provider calculation failed, falling back to route pricing', [
                'provider' => $providerName,
            ]);

            $existingRoutePricing = TenantRoutePricing::query()
                ->where('load_area_id', $loadAreaId)
                ->where('unload_area_id', $unloadAreaId)
                ->where('status', 1)
                ->whereNotNull('distance')
                ->where('distance', '>', 0)
                ->first();

            if ($existingRoutePricing) {
                \Log::info('[TenantRoutePricingService] Distance retrieved from existing route pricing', [
                    'distance' => $existingRoutePricing->distance,
                    'route_pricing_id' => $existingRoutePricing->id,
                ]);

                return $this->sendResponse(true, __('Distance retrieved from existing route pricing'), [
                    'distance' => round((float) $existingRoutePricing->distance, 2),
                    'distance_text' => round((float) $existingRoutePricing->distance, 2) . ' km',
                    'duration_text' => null,
                    'origin' => $origin,
                    'destination' => $destination,
                    'provider' => $providerName,
                    'source' => 'route_pricing',
                ]);
            }

            // STEP 3: Both provider and route pricing failed
            \Log::error('[TenantRoutePricingService] Unable to calculate distance', [
                'provider' => $providerName,
                'reason' => 'provider_failed_and_no_existing_route_pricing',
            ]);

            $providerLabel = $providerName === 'barikoi' ? 'Barikoi' : 'Google Maps';
            return $this->sendResponse(
                false,
                __("Failed to calculate distance. {$providerLabel} API failed and no existing route pricing found for this route."),
                [],
                500
            );
        } catch (Throwable $e) {
            logStore('TenantRoutePricingService calculateDistanceByGoogleMaps', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }
}
