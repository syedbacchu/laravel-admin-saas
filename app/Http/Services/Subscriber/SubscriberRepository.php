<?php

namespace App\Http\Services\Subscriber;

use App\Http\Repositories\BaseRepository;
use App\Models\Subscriber;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SubscriberRepository extends BaseRepository implements SubscriberRepositoryInterface
{
    public function __construct(Subscriber $model)
    {
        parent::__construct($model);
    }

    public function getByEmail(string $email): ?Model
    {
        return Subscriber::where('email', $email)->first();
    }

    public function dataList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: Subscriber::query(),

            searchable: [
                'email',
            ],

            filters: [
                'status' => [
                    'column' => 'status',
                ],
            ],

            select: [
                'id',
                'email',
                'status',
                'created_at',
            ],
        );
    }
}
