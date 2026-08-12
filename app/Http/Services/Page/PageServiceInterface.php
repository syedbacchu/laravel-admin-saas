<?php

namespace App\Http\Services\Page;

use App\Http\Services\BaseServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface PageServiceInterface extends BaseServiceInterface
{
    public function getDataTableData(Request $request): array;
    public function storeOrUpdatePage(Request $request): array;
    public function deletePage(int $id): array;
    public function togglePageStatus(int $id, bool $status): array;
    public function getPageWithSections(int $id): ?Model;
    public function getActivePages(): Collection;
    public function findBySlug(string $slug): ?Model;
    public function getPagesWithComponents(): Collection;
    public function getPageContent(string $slug, string $languageCode = null): array;
}