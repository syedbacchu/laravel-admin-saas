<?php

namespace App\Http\Controllers\Admin\App;

use App\Http\Controllers\Controller;
use App\Http\Services\Slider\SliderServiceInterface;
use App\Support\DataListManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use App\Enums\SliderTypeEnum;
use App\Http\Requests\Slider\SliderCreateRequest;
use App\Http\Services\Response\ResponseService;
use Illuminate\Http\RedirectResponse;

class AppSliderController extends Controller
{
    protected SliderServiceInterface $service;

    public function __construct(SliderServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): View|JsonResponse
    {
        $data['pageTitle'] = __('Slider');
        if ($request->ajax()) {
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    return $this->service
                        ->getDataTableData($request)['data']['data'];
                },
                columns: [
                    'photo' => function ($item) {
                        return '
                        <div class="flex items-center gap-2">
                          <img class="w-16 h-16 rounded-full" alt="banner" src="'.$item->photo.'">
                        </div>';
                    },

                    'created_at' => fn ($item) =>
                        $item->created_at?->diffForHumans(),

                    'published' => fn ($item) =>
                        toggle_column(
                            route('appSlider.publish'),
                            $item->id,
                            $item->published == 1
                        ),

                    'actions' => fn ($item) =>
                        action_buttons([
                            edit_column(route('appSlider.edit', $item->id)),
                            delete_column(route('appSlider.delete', $item->id)),
                        ]),
                ],
                rawColumns: ['photo', 'actions','published']
            );
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('slider','list'));
    }


    public function create()
    {
        $data['pageTitle'] = __('Create App Slider');
        $data['type'] = SliderTypeEnum::APP;
        $data['function_type'] = 'create';

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('slider','create'));
    }

    public function store(SliderCreateRequest $request): RedirectResponse {
        $response = $this->service->storeOrUpdateSlider($request);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'appSlider.list');
    }

    public function edit($id)
    {
        $data['pageTitle'] = __('Update App Slider');
        $data['type'] = SliderTypeEnum::APP;
        $data['function_type'] = 'update';
        $data['item'] = $this->service->getById($id);

        if (!$data['item'] ) {
            return ResponseService::send();
        }
        return ResponseService::send([
            'data' => $data,
        ], view: viewss('slider','create'));
    }


    public function update(SliderCreateRequest $request, string $id): RedirectResponse {

        $response = $this->service->storeOrUpdateSlider($request);
        return ResponseService::send([
            'response' => $response,
        ]);
    }

    public function destroy($id): RedirectResponse {
        $response = $this->service->deleteSlider($id);
        return ResponseService::send([
            'response' => $response,
        ]);
    }

    public function publish(Request $request): JsonResponse {
        try {
            $response = $this->service->publishSlider($request->id,$request->status);
            return response()->json($response);
        } catch (\Exception $e) {
            logStore('Slider destroy',$e->getMessage());
            return response()->json(['success'=>false,'message'=>somethingWrong()]);
        }
    }


}
