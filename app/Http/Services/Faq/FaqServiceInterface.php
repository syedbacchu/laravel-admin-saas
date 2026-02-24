<?php

namespace App\Http\Services\Faq;

use App\Http\Requests\Faq\FaqCreateRequest;
use App\Http\Services\BaseServiceInterface;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

interface FaqServiceInterface extends BaseServiceInterface
{

    public function getDataTableData($request): array;
    public function storeOrUpdateFaq(FaqCreateRequest $request): array;
    public function deleteFaq($id): array;
    public function publishFaq($id, $status): array;
    public function faqEditData($id): array;
    public function faqCreateData($guard): array;


}
