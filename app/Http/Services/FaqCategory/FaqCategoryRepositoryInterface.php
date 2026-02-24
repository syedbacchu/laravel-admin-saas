<?php

namespace App\Http\Services\FaqCategory;

use App\Http\Repositories\BaseRepositoryInterface;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

interface FaqCategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function faqCategoryList($request): array;
    public function createFaqCategory(array $data): Model;
}
