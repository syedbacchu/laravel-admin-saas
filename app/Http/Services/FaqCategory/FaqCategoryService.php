<?php

namespace App\Http\Services\FaqCategory;

use App\Enums\StatusEnum;
use App\Http\Requests\Faq\FaqCategoryCreateRequest;
use App\Http\Services\BaseService;
use App\Traits\FileUploadTrait;

class FaqCategoryService extends BaseService implements FaqCategoryServiceInterface
{
    use FileUploadTrait;

    protected FaqCategoryRepositoryInterface $faqCategoryRepository;

    public function __construct(FaqCategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->faqCategoryRepository = $repository; // use this specifically
    }
    public function storeOrUpdateFaqCategory(FaqCategoryCreateRequest $request): array
    {
        $data = [
            'name'        => $request->name,
            'description' => $request->description,
            'sort_order'  => $request->sort_order ?? 0,
            'status'      => $request->status ?? StatusEnum::ACTIVE,
        ];

        if ($request->edit_id) {

            $item = $this->faqCategoryRepository->find($request->edit_id);

            if ($item) {

                //  IMAGE FROM FILE MANAGER (STRING URL)
                if (!empty($request->image)) {
                    $data['image'] = $request->image;
                }

                $this->faqCategoryRepository->update($item->id, $data);
                $message = __('FaqCategory updated successfully');
            } else {
                return $this->sendResponse(false, __('Data not found'));
            }
        } else {

            //  IMAGE FROM FILE MANAGER (STRING URL)
            if (!empty($request->image)) {
                $data['image'] = $request->image;
            }

            $item = $this->faqCategoryRepository->createFaqCategory($data);
            $message = __('FaqCategory created successfully');
        }

        return $this->sendResponse(true, $message, $item ?? null);
    }


    public function deleteFaqCategory($id): array
    {
        $item = $this->faqCategoryRepository->find($id);
        if ($item) {
            $this->faqCategoryRepository->delete($id);
            return $this->sendResponse(true, __('Data deleted successfully'));
        }

        return $this->sendResponse(false, __('Data not found'));
    }

    public function publishFaqCategory($id, $status): array
    {
        $item = $this->faqCategoryRepository->find($id);
        if ($item) {
            $this->faqCategoryRepository->update($id, ['status' => $status]);
            return $this->sendResponse(true, __('Status updated successfully'));
        }

        return $this->sendResponse(false, __('Data not found'));
    }

    public function getDataTableData($request): array
    {
        return $this->faqCategoryRepository->faqCategoryList($request);
    }

    public function faqCategoryEditData($id): array
    {
        $item = $this->faqCategoryRepository->find($id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        return $this->sendResponse(true, '', $item);
    }

    public function faqCategoryCreateData($guard): array
    {
        return $this->sendResponse(true, '', []);
    }
}
