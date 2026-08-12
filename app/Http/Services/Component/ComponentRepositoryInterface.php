<?php

namespace App\Http\Services\Component;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\Component;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface ComponentRepositoryInterface extends BaseRepositoryInterface
{
    public function componentList(Request $request): array;
    public function findBySlug(string $slug): ?Component;
    public function getActiveComponents(): Collection;
    public function updateStatus(int $id, bool $status): bool;
}