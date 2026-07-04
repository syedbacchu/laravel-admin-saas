<?php

namespace App\Http\Services\TenantSetting;

use App\Http\Repositories\BaseRepository;
use App\Models\Tenant\TenantSetting;

class TenantSettingRepository extends BaseRepository implements TenantSettingRepositoryInterface
{
    public function __construct(TenantSetting $model)
    {
        parent::__construct($model);
    }

    public function getSettings(array $slugs = []): array
    {
        $query = TenantSetting::query()->select(['slug', 'value']);

        if ($slugs !== []) {
            $query->whereIn('slug', $slugs);
        }

        return $query->pluck('value', 'slug')->toArray();
    }

    public function upsertSettings(array $settings, int $actorId = 0): void
    {
        foreach ($settings as $slug => $value) {
            $item = TenantSetting::query()->firstOrNew(['slug' => $slug]);
            $item->value = $value;

            if (!$item->exists && $actorId > 0) {
                $item->added_by = $actorId;
            }

            if ($actorId > 0) {
                $item->updated_by = $actorId;
            }

            $item->save();
        }
    }

    public function deleteSettings(array $slugs): int
    {
        return TenantSetting::query()->whereIn('slug', $slugs)->delete();
    }
}
