<?php

namespace App\Http\Services\Slider;

use App\Enums\StatusEnum;
use App\Enums\UploadFolderEnum;
use App\Http\Requests\Slider\SliderCreateRequest;
use App\Http\Services\BaseService;
use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SliderService extends BaseService implements SliderServiceInterface
{
    use FileUploadTrait;

    protected SliderRepositoryInterface $sliderRepository;

    public function __construct(SliderRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->sliderRepository = $repository; // use this specifically
    }

    public function getDataTableData(Request $request): array
    {
        $data = $this->sliderRepository->dataList($request);
        return $this->sendResponse(true,__('Data get successfully.'),$data);
    }

    public function storeOrUpdateSlider(SliderCreateRequest $request): array
    {
        $item = "";
        $data = [
            'type' => $request->type,
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'description' => $request->description,
            'tagline' => $request->tagline,
            'link' => $request->link,
            'position' => $request->position,
            'page' => $request->page,
            'video_link' => $request->video_link,
            'serial' => $request->serial ?? 0,
            'published' => $request->published ?? StatusEnum::ACTIVE,
            'mobile_banner' => $request->mobile_banner ?? null,
            'cta_button' => $request->cta_button ?? null,
            'stat' => $request->stat ?? null,
        ];
        $message = "";
        if ($request->edit_id) {
            $existItem = $this->sliderRepository->find($request->edit_id);
            if ($existItem) {
                if ($request->photo) {
                    $data['photo'] = $request->photo;
                }
                $this->sliderRepository->update($existItem->id,$data);
                $item = $this->sliderRepository->find($existItem->id);
                \Illuminate\Support\Facades\Cache::flush();
                $message = __('Slider updated successfully');
            } else {
                return $this->sendResponse(false,__('Data not found'));
            }
        } else {
            if ($request->photo) {
                $data['photo'] = $request->photo;
            }
            $item = $this->sliderRepository->createSlider($data);
            \Illuminate\Support\Facades\Cache::flush();
            $message = __('Slider created successfully');
        }

        return $this->sendResponse(true,$message,$item);
    }

    public function deleteSlider($id): array
    {
        $item = $this->sliderRepository->find($id);
        if ($item) {
            $this->delete($item->id);
            return $this->sendResponse(true,__('Slider deleted successfully'));
        } else {
            return $this->sendResponse(false,__('Data not found'));
        }
    }

     public function publishSlider($id,$status): array
     {
        $item = $this->sliderRepository->find($id);
        if ($item) {
            $this->update($item->id,['status' => $status]);
            return $this->sendResponse(true,__('Status updated successfully'));
        } else {
            return $this->sendResponse(false,__('Data not found'));
        }
     }

    public function getPublicList(Request $request): array
    {
        $request->merge(['status' => $request->status ?? StatusEnum::ACTIVE->value]);
        $data = $this->sliderRepository->dataList($request);
        // Force clear any existing cache
        \Illuminate\Support\Facades\Cache::flush();

        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function getPublicDetails(string $identifier): array
    {
        $item = $this->sliderRepository->findPublicByIdentifier($identifier);

        if (!$item) {
            return $this->sendResponse(false, __('Slider not found'), [], 404, __('Slider not found'));
        }

        return $this->sendResponse(true, __('Slider details'), $item);
    }
}
