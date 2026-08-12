<?php

namespace App\Http\Services\Component;

use App\Http\Services\BaseServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface ComponentServiceInterface extends BaseServiceInterface
{
    public function getDataTableData(Request $request): array;
    public function storeOrUpdateComponent(Request $request): array;
    public function deleteComponent(int $id): array;
    public function publishComponent(int $id, bool $status): array;
    public function getComponentWithFields(int $id): ?Model;
    public function getActiveComponents(): Collection;
    public function findBySlug(string $slug): ?Model;
}