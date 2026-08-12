<?php

namespace App\Http\Services\PageSection;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\PageSection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface PageSectionRepositoryInterface extends BaseRepositoryInterface
{
    public function sectionList(Request $request, int $pageId): array;
    public function getPageSections(int $pageId): Collection;
    public function getSectionsWithTranslations(int $pageId): Collection;
    public function createSection(array $data): PageSection;
    public function updateSection(int $id, array $data): bool;
    public function updateSortOrder(array $sectionIds): bool;
    public function getVisibleSections(int $pageId): Collection;
    public function getMaxSortOrder(int $pageId): int;
}
