<?php

namespace App\Http\Services\Page;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface PageRepositoryInterface extends BaseRepositoryInterface
{
    public function pageList(Request $request): array;
    public function findBySlug(string $slug): ?Page;
    public function getActivePages(): Collection;
    public function getPagesWithSections(): Collection;
    public function updateStatus(int $id, bool $status): bool;
    public function getPagesWithComponents(): Collection;
}