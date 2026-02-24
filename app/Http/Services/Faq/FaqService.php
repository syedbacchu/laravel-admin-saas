<?php

namespace App\Http\Services\Faq;

use App\Enums\StatusEnum;
use App\Http\Requests\Faq\FaqCreateRequest;
use App\Http\Services\BaseService;
use App\Traits\FileUploadTrait;

class FaqService extends BaseService implements FaqServiceInterface
{
    use FileUploadTrait;

    protected FaqRepositoryInterface $faqRepository;

    public function __construct(FaqRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->faqRepository = $repository; // use this specifically
    }
    public function storeOrUpdateFaq(FaqCreateRequest $request): array
{
    $data = [
        'category_id' => $request->category_id, 
        'question'    => $request->question,
        'answer'      => $request->answer,
        'attestment'  => $request->attestment ?? null,
        'sort_order'  => $request->sort_order ?? 0,
        'status'      => $request->status ?? StatusEnum::ACTIVE,
    ];

    if ($request->edit_id) {
        $item = $this->faqRepository->find($request->edit_id);

        if ($item) {
            $this->faqRepository->update($item->id, $data);
            $message = __('Faq updated successfully');
        } else {
            return $this->sendResponse(false, __('Data not found'));
        }
    } else {
        $item = $this->faqRepository->createFaq($data);
        $message = __('Faq created successfully');
    }

    return $this->sendResponse(true, $message, $item ?? null);
}



    public function deleteFaq($id): array
    {
        $item = $this->faqRepository->find($id);
        if ($item) {
            $this->faqRepository->delete($id);
            return $this->sendResponse(true, __('Data deleted successfully'));
        }

        return $this->sendResponse(false, __('Data not found'));
    }

    public function publishFaq($id, $status): array
    {
        $item = $this->faqRepository->find($id);
        if ($item) {
            $this->faqRepository->update($id, ['status' => $status]);
            return $this->sendResponse(true, __('Status updated successfully'));
        }

        return $this->sendResponse(false, __('Data not found'));
    }

    public function getDataTableData($request): array
    {
        return $this->faqRepository->faqList($request);
    }

    public function faqEditData($id): array
    {
        $item = $this->faqRepository->find($id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        return $this->sendResponse(true, '', $item);
    }

    public function faqCreateData($guard): array
    {
        return $this->sendResponse(true, '', []);
    }
}
