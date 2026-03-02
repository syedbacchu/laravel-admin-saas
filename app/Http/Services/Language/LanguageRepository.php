<?php

namespace App\Http\Services\Language;

use App\Http\Repositories\BaseRepository;
use App\Models\Language;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class LanguageRepository extends BaseRepository implements LanguageRepositoryInterface
{
    public function __construct(Language $model)
    {
        parent::__construct($model);
    }

    public function languageList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: Language::query(),
            searchable: [
                'name',
                'native_name',
                'code',
            ],
            filters: [
                'status' => [
                    'column' => 'status',
                ],
            ],
            select: [
                'id',
                'name',
                'native_name',
                'code',
                'direction',
                'sort_order',
                'status',
                'is_default',
                'created_at',
            ],
        );
    }

    public function createLanguage(array $data): Model
    {
        return $this->create($data);
    }
}

