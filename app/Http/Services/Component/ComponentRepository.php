<?php

namespace App\Http\Services\Component;

use App\Http\Repositories\BaseRepository;
use App\Models\Component;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ComponentRepository extends BaseRepository implements ComponentRepositoryInterface
{
    public function __construct(Component $model)
    {
        parent::__construct($model);
    }

    public function componentList(Request $request): array
    {
        $query = Component::query()->withCount('fields');

        return DataListManager::list(
            request: $request,
            query: $query,
            searchable: [
                'name',
                'slug',
                'description',
            ],
            filters: [
                'status' => [
                    'column' => 'status',
                ],
            ],
            select: [
                'id',
                'name',
                'slug',
                'description',
                'status',
                'created_at',
                'updated_at',
            ],
        );
    }

    public function findBySlug(string $slug): ?Component
    {
        return Component::where('slug', $slug)->first();
    }

    public function getActiveComponents(): Collection
    {
        return Component::where('status', true)
            ->with('parentFields')
            ->orderBy('name')
            ->get();
    }

    public function updateStatus(int $id, bool $status): bool
    {
        $component = $this->find($id);
        if ($component) {
            return $component->update(['status' => $status]);
        }
        return false;
    }
}