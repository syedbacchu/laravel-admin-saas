<?php

namespace App\Http\Services\SectionTranslation;

use App\Http\Repositories\BaseRepository;
use App\Models\SectionTranslation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SectionTranslationRepository extends BaseRepository implements SectionTranslationRepositoryInterface
{
    public function __construct(SectionTranslation $model)
    {
        parent::__construct($model);
    }

    public function getTranslationsBySection(int $sectionId): Collection
    {
        return SectionTranslation::where('page_section_id', $sectionId)
            ->with('language')
            ->get();
    }

    public function getTranslationBySectionAndLanguage(int $sectionId, int $languageId): ?SectionTranslation
    {
        return SectionTranslation::where('page_section_id', $sectionId)
            ->where('language_id', $languageId)
            ->first();
    }

    public function createTranslation(array $data): SectionTranslation
    {
        return $this->create($data);
    }

    public function updateTranslation(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    public function deleteTranslationsBySection(int $sectionId): bool
    {
        try {
            SectionTranslation::where('page_section_id', $sectionId)->delete();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
