<?php

namespace App\Http\Services\TenantSetting;

use Illuminate\Http\Request;

interface TenantSettingServiceInterface
{
    public function settingList(Request $request): array;

    public function upsertSettings(Request $request): array;

    public function deleteSettings(Request $request): array;
}
