<?php

namespace App\Http\Services\Slider;

use App\Http\Repositories\BaseRepositoryInterface;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface SliderRepositoryInterface extends BaseRepositoryInterface
{
    public function dataList(Request $request): array;
    public function createSlider(array $data): Model;
}
