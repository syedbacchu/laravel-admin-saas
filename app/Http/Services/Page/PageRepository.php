<?php

namespace App\Http\Services\Page;

use App\Enums\StatusEnum;
use App\Http\Repositories\BaseRepository;
use App\Models\Page;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PageRepository extends BaseRepository implements PageRepositoryInterface
{
    public function __construct(Page $model)
    {
        parent::__construct($model);
    }

    public function pageList(Request $request): array
    {
        $query = Page::query()->withCount('sections');

        return DataListManager::list(
            request: $request,
            query: $query,
            searchable: [
                'name',
                'slug',
                'meta_title',
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
                'meta_title',
                'meta_description',
                'meta_image',
                'status',
                'created_at',
                'updated_at',
            ],
        );
    }

    public function findBySlug(string $slug): ?Page
    {
        return Page::where('slug', $slug)->first();
    }

    public function getActivePages(): Collection
    {
        return Page::where('status', true)
            ->with(['sections' => function ($query) {
                $query->orderBy('sort_order')->with('component');
            }])
            ->orderBy('name')
            ->get();
    }

    public function getPagesWithSections(): Collection
    {
        return Page::with(['sections' => function ($query) {
                $query->orderBy('sort_order')->with('component');
        }])->orderBy('name')->get();
    }

    public function updateStatus(int $id, bool $status): bool
    {
        $page = $this->find($id);
        if ($page) {
            return $page->update(['status' => $status]);
        }
        return false;
    }

    public function getPagesWithComponents(): Collection
    {
        return Page::with(['sections.component', 'sections.translations'])->orderBy('name')->get();
    }

    public function findPageWithSectionsBySlug(string $slug, int $languageId)
    {
        return Page::where('slug', $slug)
            ->where('status', enum(StatusEnum::ACTIVE))
            ->with(['activeSections.component', 'activeSections.translations' => function ($query) use ($languageId) {
                $query->where('language_id', $languageId);
            }])
            ->first();
    }
}
