<?php

namespace App\Http\Services\Faq;

use App\Http\Repositories\BaseRepository;
use App\Models\Faq;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class FaqRepository extends BaseRepository implements FaqRepositoryInterface
{
    public function __construct(Faq $model)
    {
        parent::__construct($model);
    }

    public function faqList($request): array
    {
        $query = Faq::query();

        if ($request->get('status') !== null) {
            $query->where('status', $request->get('status'));
        }

        if ($search = $request->get('search')) {
            if (is_array($search)) {
                $search = $search['value'] ?? null;
            }

            if ($search) {
                $query->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            }
        }
        // IMPORTANT: no ->get()
        $items = $query->orderBy('id', 'desc');

        return [
            'data' => $items
        ];
    }


    public function createFaq(array $data): Model
    {
        return $this->create($data);
    }

    public function delete($id): bool
    {
        $faq = Faq::find($id);
        if ($faq) {
            return $faq->delete(); // true/false
        }
        return false;
    }
}
