<?php

namespace App\Http\Services\Testimonial;

use App\Http\Requests\Testimonial\TestimonialCreateRequest;
use App\Http\Services\BaseServiceInterface;
use Illuminate\Http\Request;

interface TestimonialServiceInterface extends BaseServiceInterface
{
    public function storeOrUpdate(TestimonialCreateRequest $request): array;
    public function deleteData($id): array;
    public function publish($id, $status): array;
    public function getDataTableData(Request $request): array;
    public function editData($id): array;
    public function createData(Request $request): array;
    public function getPublicList(Request $request): array;
    public function getPublicDetails(string $identifier): array;
}
