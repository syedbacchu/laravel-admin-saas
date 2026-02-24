<?php

namespace App\Http\Services\Slider;

use App\Http\Requests\Slider\SliderCreateRequest;
use App\Http\Services\BaseServiceInterface;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

interface SliderServiceInterface extends BaseServiceInterface
{

    public function getDataTableData(Request $request): array;
    public function storeOrUpdateSlider(SliderCreateRequest $request): array; // For store producty
    public function deleteSlider($id): array; // For delete producty
    public function publishSlider($id,$status): array; // For status producty

}
