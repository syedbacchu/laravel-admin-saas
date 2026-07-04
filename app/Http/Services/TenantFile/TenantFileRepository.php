<?php

namespace App\Http\Services\TenantFile;

use App\Http\Repositories\BaseRepository;
use App\Models\TenantFile;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TenantFileRepository extends BaseRepository implements TenantFileRepositoryInterface
{
    public function __construct(TenantFile $model)
    {
        parent::__construct($model);
    }

    public function fileList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: TenantFile::query(),
            searchable: [
                'filename',
                'original_name',
                'alt_text',
                'title',
                'description',
                'seo_keywords',
                'seo_title',
                'seo_description',
            ],
            filters: [
                'uploaded_by' => [
                    'column' => 'uploaded_by',
                ],
                'extension' => [
                    'column' => 'extension',
                ],
                'type' => [
                    'column' => 'type',
                ],
                'created_at' => [
                    'column' => 'created_at',
                    'type' => 'date',
                ],
            ],
            select: [
                'id',
                'filename',
                'original_name',
                'type',
                'extension',
                'size',
                'path',
                'full_url',
                'dimensions',
                'alt_text',
                'title',
                'description',
                'seo_keywords',
                'seo_title',
                'seo_description',
                'uploaded_by',
                'created_at',
                'updated_at',
            ],
        );
    }

    public function createTenantFile(array $data): Model
    {
        return $this->create($data);
    }

    public function findTenantFile(int $id): ?TenantFile
    {
        return TenantFile::query()->find($id);
    }
}
