<?php

namespace App\Http\Services\FaqCategory;

use App\Http\Requests\Faq\FaqCategoryCreateRequest;
use App\Http\Services\BaseServiceInterface;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

interface FaqCategoryServiceInterface extends BaseServiceInterface
{

    public function getDataTableData($request): array;
    public function storeOrUpdateFaqCategory(FaqCategoryCreateRequest $request): array;
    public function deleteFaqCategory($id): array;
    public function publishFaqCategory($id, $status): array;
    public function faqCategoryEditData($id): array;
    public function faqCategoryCreateData($guard): array;


}
