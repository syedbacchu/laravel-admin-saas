<?php

namespace App\Http\Services\Subscriber;

use App\Http\Repositories\BaseRepositoryInterface;
use Illuminate\Http\Request;

interface SubscriberRepositoryInterface extends BaseRepositoryInterface
{
    public function getByEmail(string $email);
    public function dataList(Request $request): array;
}
