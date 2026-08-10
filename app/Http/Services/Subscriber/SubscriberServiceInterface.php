<?php

namespace App\Http\Services\Subscriber;

use App\Http\Services\BaseServiceInterface;

interface SubscriberServiceInterface extends BaseServiceInterface
{
    public function subscribe(array $data): array;
    public function getDataTableData($request): array;
    public function toggleStatus(int $id): array;
}
