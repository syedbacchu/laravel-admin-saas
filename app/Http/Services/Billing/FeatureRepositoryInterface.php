<?php

namespace App\Http\Services\Billing;

use App\Http\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface FeatureRepositoryInterface extends BaseRepositoryInterface
{
    public function featureList(Request $request): array;
    public function createFeature(array $data): Model;
}
