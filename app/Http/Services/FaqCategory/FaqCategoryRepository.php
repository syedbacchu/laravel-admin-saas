<?php

namespace App\Http\Services\FaqCategory;

use App\Http\Repositories\BaseRepository;
use App\Models\FaqCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class FaqCategoryRepository extends BaseRepository implements FaqCategoryRepositoryInterface
{
    public function __construct(FaqCategory $model)
    {
        parent::__construct($model);
    }

    public function faqCategoryList($request): array
    {
        $query = FaqCategory::query();

        if ($request->get('status') !== null) {
            $query->where('status', $request->get('status'));
        }

        if ($search = $request->get('search')) {
            // DataTable search fix
            if (is_array($search)) {
                $search = $search['value'] ?? null;
            }

            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }
        }

        // IMPORTANT: no ->get()
        $items = $query->orderBy('id', 'desc');

        return [
            'data' => $items
        ];
    }


    public function createFaqCategory(array $data): Model
    {
        return $this->create($data);
    }
}
