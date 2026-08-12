<?php

namespace App\Http\Services\SectionTranslation;

use App\Http\Services\BaseServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface SectionTranslationServiceInterface extends BaseServiceInterface
{
    public function getTranslationsBySection(int $sectionId): Collection;
    public function getTranslationBySectionAndLanguage(int $sectionId, int $languageId): ?\App\Models\SectionTranslation;
    public function createTranslation(array $data): \App\Models\SectionTranslation;
    public function updateTranslation(int $id, array $data): bool;
    public function deleteTranslationsBySection(int $sectionId): bool;
    public function getTranslationDetail(int $id): ?Model;
    public function storeOrUpdateTranslation(Request $request, int $sectionId): array;
    public function deleteTranslation(int $id): array;
    public function saveTranslationData(int $sectionId, int $languageId, array $data): array;
    public function saveTranslationContent(int $sectionId, int $languageId, array $data): array;
}
