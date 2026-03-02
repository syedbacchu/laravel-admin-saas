<?php

namespace App\Http\Services\Language;

use App\Http\Requests\Language\LanguageCreateRequest;

interface LanguageServiceInterface
{
    public function getDataTableData($request): array;
    public function storeOrUpdateLanguage(LanguageCreateRequest $request): array;
    public function deleteLanguage($id): array;
    public function languageEditData($id): array;
    public function publishLanguage($id, $status): array;
}

