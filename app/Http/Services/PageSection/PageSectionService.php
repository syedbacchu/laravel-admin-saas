<?php

namespace App\Http\Services\PageSection;

use App\Http\Services\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PageSectionService extends BaseService implements PageSectionServiceInterface
{

    public function __construct(PageSectionRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->repository = $repository;
    }

    public function getDataTableData(Request $request, int $pageId): array
    {
        $request->merge(['orderBy' => 'asc', 'orderColumn' => 'sort_order']);
        $data = $this->repository->sectionList($request, $pageId);
        return $this->sendResponse(true, __('Data retrieved successfully'), $data);
    }

    public function sectionList(Request $request, int $pageId): array
    {
        $request->merge(['orderBy' => 'asc', 'orderColumn' => 'sort_order']);
        return $this->repository->sectionList($request, $pageId);
    }

    public function storeOrUpdateSection(Request $request, int $pageId): array
    {
        $data = [
            'page_id' => $pageId,
            'component_id' => $request->component_id,
            'sort_order' => $request->sort_order ?? ($this->repository->getMaxSortOrder($pageId) + 1),
            'is_visible' => $request->is_visible ?? true,
        ];

        if ($request->edit_id) {
            $existingSection = $this->repository->find($request->edit_id);
            if (!$existingSection) {
                return $this->sendResponse(false, __('Section not found'));
            }

            $this->repository->update($existingSection->id, $data);
            $section = $this->repository->find($existingSection->id);
            return $this->sendResponse(true, __('Section updated successfully'), $section);
        } else {
            $section = $this->repository->create($data);
            return $this->sendResponse(true, __('Section created successfully'), $section);
        }
    }

    public function getSectionDetail(int $id): ?Model
    {
        return $this->repository->find($id);
    }

    public function toggleSectionVisibility(int $id, bool $status): array
    {
        $section = $this->repository->find($id);
        if (!$section) {
            return $this->sendResponse(false, __('Section not found'));
        }

        $this->repository->update($id, ['is_visible' => $status]);
        return $this->sendResponse(true, __('Section visibility updated successfully'));
    }

    public function updateSectionSortOrder(Request $request): array
    {
        $sectionIds = $request->input('section_ids', []);
        $result = $this->repository->updateSortOrder($sectionIds);

        if ($result) {
            return $this->sendResponse(true, __('Sort order updated successfully'));
        } else {
            return $this->sendResponse(false, __('Failed to update sort order'));
        }
    }

    public function getPageSections(int $pageId): Collection
    {
        return $this->repository->getPageSections($pageId);
    }

    public function getSectionsWithTranslations(int $pageId): Collection
    {
        return $this->repository->getSectionsWithTranslations($pageId);
    }

    public function createSection(array $data): \App\Models\PageSection
    {
        return $this->repository->createSection($data);
    }

    public function updateSection(int $id, array $data): bool
    {
        return $this->repository->updateSection($id, $data);
    }

    public function updateSortOrder(array $sectionIds): bool
    {
        return $this->repository->updateSortOrder($sectionIds);
    }

    public function getVisibleSections(int $pageId): Collection
    {
        return $this->repository->getVisibleSections($pageId);
    }

    public function deleteSection(int $id): bool
    {
        try {
            DB::beginTransaction();

            $section = \App\Models\PageSection::find($id);
            if (!$section) {
                return false;
            }

            // Delete translations first
            $section->translations()->delete();

            // Delete the section
            $result = $this->delete($id);

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}
