<?php

namespace App\Http\Services\PageSection;

use App\Http\Repositories\BaseRepository;
use App\Models\PageSection;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PageSectionRepository extends BaseRepository implements PageSectionRepositoryInterface
{
    public function __construct(PageSection $model)
    {
        parent::__construct($model);
    }

    public function sectionList(Request $request, int $pageId): array
    {
        $query = PageSection::where('page_id', $pageId)
            ->with(['component', 'translations' => function ($query) {
                $query->orderBy('language_id');
            }]);

        return DataListManager::list(
            request: $request,
            query: $query,
            searchable: [
                // No direct searchable fields on PageSection
                // Component names are handled via relationship, not search
            ],
            filters: [
                'is_visible' => [
                    'column' => 'is_visible',
                ],
                'component_id' => [
                    'column' => 'component_id',
                ],
            ],
            select: [
                'id',
                'page_id',
                'component_id',
                'sort_order',
                'is_visible',
                'created_at',
            ],
        );
    }

    public function getPageSections(int $pageId): Collection
    {
        return PageSection::where('page_id', $pageId)
            ->with(['component', 'translations'])
            ->orderBy('sort_order')
            ->get();
    }

    public function getSectionsWithTranslations(int $pageId): Collection
    {
        return PageSection::where('page_id', $pageId)
            ->with(['component', 'translations.language'])
            ->orderBy('sort_order')
            ->get();
    }

    public function createSection(array $data): PageSection
    {
        return $this->create($data);
    }

    public function updateSection(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    public function updateSortOrder(array $sectionIds): bool
    {
        try {
            DB::beginTransaction();

            foreach ($sectionIds as $index => $sectionId) {
                PageSection::where('id', $sectionId)->update([
                    'sort_order' => $index + 1
                ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function getVisibleSections(int $pageId): Collection
    {
        return PageSection::where('page_id', $pageId)
            ->where('is_visible', true)
            ->with(['component', 'translations.language'])
            ->orderBy('sort_order')
            ->get();
    }

    public function getMaxSortOrder(int $pageId): int
    {
        return PageSection::where('page_id', $pageId)->max('sort_order') ?? 0;
    }
}