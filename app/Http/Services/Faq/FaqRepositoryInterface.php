<?php

namespace App\Http\Services\Faq;

use App\Http\Repositories\BaseRepositoryInterface;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

interface FaqRepositoryInterface extends BaseRepositoryInterface
{
    public function faqList($request): array;
    public function createFaq(array $data): Model;
    public function delete($id): bool;
}
