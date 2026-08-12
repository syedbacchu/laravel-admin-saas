<?php

namespace App\Http\Services\PageSection;

use App\Http\Services\BaseServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface PageSectionServiceInterface extends BaseServiceInterface
{
    public function getDataTableData(Request $request, int $pageId): array;
    public function sectionList(Request $request, int $pageId): array;
    public function getPageSections(int $pageId): Collection;
    public function getSectionsWithTranslations(int $pageId): Collection;
    public function createSection(array $data): \App\Models\PageSection;
    public function updateSection(int $id, array $data): bool;
    public function updateSortOrder(array $sectionIds): bool;
    public function getVisibleSections(int $pageId): Collection;
}
