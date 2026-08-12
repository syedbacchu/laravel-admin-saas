<?php

namespace App\Http\Services\SectionTranslation;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\SectionTranslation;
use Illuminate\Database\Eloquent\Collection;

interface SectionTranslationRepositoryInterface extends BaseRepositoryInterface
{
    public function getTranslationsBySection(int $sectionId): Collection;
    public function getTranslationBySectionAndLanguage(int $sectionId, int $languageId): ?SectionTranslation;
    public function createTranslation(array $data): SectionTranslation;
    public function updateTranslation(int $id, array $data): bool;
    public function deleteTranslationsBySection(int $sectionId): bool;
}
