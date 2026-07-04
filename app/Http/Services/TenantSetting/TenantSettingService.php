<?php

namespace App\Http\Services\TenantSetting;

use App\Http\Services\BaseService;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class TenantSettingService extends BaseService implements TenantSettingServiceInterface
{
    protected TenantSettingRepositoryInterface $tenantSettingRepository;

    public function __construct(TenantSettingRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->tenantSettingRepository = $repository;
    }

    public function settingList(Request $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $slugs = $this->extractSlugList($request);
        $data = $this->tenantSettingRepository->getSettings($slugs);

        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function upsertSettings(Request $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $validated = $request->validate([
            'settings' => ['required', 'array', 'min:1'],
            'settings.*.slug' => ['required', 'string', 'max:180', 'regex:/^[A-Za-z0-9_-]+$/'],
            'settings.*.value' => ['nullable'],
        ]);

        try {
            $actorId = (int) ($request->user()?->id ?? 0);
            $settings = [];

            foreach ($validated['settings'] as $item) {
                $settings[strtolower(trim((string) $item['slug']))] = $this->normalizeSettingValue($item['value'] ?? null);
            }

            DB::transaction(function () use ($settings, $actorId) {
                $this->tenantSettingRepository->upsertSettings($settings, $actorId);
            });

            return $this->sendResponse(true, __('Tenant settings saved successfully'), $this->tenantSettingRepository->getSettings(array_keys($settings)));
        } catch (Throwable $e) {
            logStore('TenantSettingService upsertSettings', $e->getMessage());

            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function deleteSettings(Request $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $validated = $request->validate([
            'slugs' => ['required', 'array', 'min:1'],
            'slugs.*' => ['required', 'string', 'max:180', 'regex:/^[A-Za-z0-9_-]+$/'],
        ]);

        try {
            $slugs = array_values(array_unique(array_map(
                static fn (string $slug): string => strtolower(trim($slug)),
                $validated['slugs']
            )));

            $deletedCount = $this->tenantSettingRepository->deleteSettings($slugs);

            return $this->sendResponse(true, __('Tenant settings deleted successfully'), [
                'deleted_count' => $deletedCount,
                'slugs' => $slugs,
            ]);
        } catch (Throwable $e) {
            logStore('TenantSettingService deleteSettings', $e->getMessage());

            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    protected function resolveTenantFromRequest(Request $request): ?Tenant
    {
        $tenant = $request->attributes->get('tenant');
        return $tenant instanceof Tenant ? $tenant : null;
    }

    protected function normalizeSettingValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return (string) $value;
    }

    protected function extractSlugList(Request $request): array
    {
        $slugs = $request->input('slugs', $request->query('slugs', []));

        if (is_string($slugs)) {
            $slugs = array_filter(array_map('trim', explode(',', $slugs)));
        }

        if (!is_array($slugs)) {
            return [];
        }

        $validated = validator(
            ['slugs' => $slugs],
            [
                'slugs' => ['nullable', 'array'],
                'slugs.*' => ['required', 'string', 'max:180', 'regex:/^[A-Za-z0-9_-]+$/'],
            ]
        )->validate();

        return array_values(array_unique(array_map(
            static fn (string $slug): string => strtolower(trim($slug)),
            $validated['slugs'] ?? []
        )));
    }
}
