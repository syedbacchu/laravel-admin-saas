<?php

namespace App\Http\Services\Testimonial;

use App\Http\Requests\Testimonial\TestimonialCreateRequest;
use App\Http\Services\BaseService;
use App\Http\Services\Testimonial\TestimonialRepositoryInterface;
use App\Models\Testimonial;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TestimonialService extends BaseService implements TestimonialServiceInterface
{
    protected TestimonialRepositoryInterface $testimonialRepository;

    public function __construct(TestimonialRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->testimonialRepository = $repository;
    }

    public function storeOrUpdate(TestimonialCreateRequest $request): array
    {
        $editId = $request->edit_id;

        $data = [
            'name' => $request->name,
            'review_text' => $request->review_text,
            'review_star' => $request->review_star,
            'designation' => $request->designation,
            'image' => $request->image,
            'sort_order' => $request->sort_order ?? 0,
            'status' => $request->status ?? 1,
        ];

        if ($editId) {
            $data['updated_by'] = auth()->id();
            $this->testimonialRepository->update($editId, $data);
            return $this->sendResponse(true, 'Updated successfully');
        }

        $data['created_by'] = auth()->id();
        $this->testimonialRepository->create($data);

        return $this->sendResponse(true, 'Created successfully');
    }

    public function deleteData($id): array
    {
        $item = $this->testimonialRepository->find($id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        $this->testimonialRepository->delete($id);
        return $this->sendResponse(true, __('Data deleted successfully'));
    }

    public function publish($id, $status): array
    {
        $item = $this->testimonialRepository->find($id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        $this->testimonialRepository->update($id, ['status' => (int) $status]);
        return $this->sendResponse(true, __('Status updated successfully'));
    }

    public function getDataTableData($request): array
    {
        $data = $this->testimonialRepository->dataList($request);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function editData($id): array
    {
        $item = $this->testimonialRepository->find($id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        return $this->sendResponse(true, '', $item);
    }

    public function createData(Request $request): array
    {
        $categories = [];

        return $this->sendResponse(true, '', [
            'categories' => $categories,
        ]);
    }

    public function getPublicList(Request $request): array
    {
        $request->merge(['status' => $request->status ?? 1]);
        $data = $this->testimonialRepository->dataList($request);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function getPublicDetails(string $identifier): array
    {
        $item = $this->testimonialRepository->findPublicByIdentifier($identifier);

        if (!$item) {
            return $this->sendResponse(false, __('Testimonial not found'), [], 404, __('Testimonial not found'));
        }

        return $this->sendResponse(true, __('Testimonial details'), $item);
    }

    protected function generateUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'testimonial';

        if ($ignoreId) {
            $current = Testimonial::query()->find($ignoreId, ['id', 'slug']);
            if ($current && $current->slug === $base) {
                return $base;
            }
        }

        return make_unique_slug($base, 'testimonials');
    }
}
