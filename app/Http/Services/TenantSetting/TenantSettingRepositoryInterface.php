<?php

namespace App\Http\Services\TenantSetting;

use App\Http\Repositories\BaseRepositoryInterface;

interface TenantSettingRepositoryInterface extends BaseRepositoryInterface
{
    public function getSettings(array $slugs = []): array;

    public function upsertSettings(array $settings, int $actorId = 0): void;

    public function deleteSettings(array $slugs): int;
}
