<?php

namespace App\Http\Controllers\Admin\Testimonial;

use App\Http\Controllers\Controller;
use App\Http\Requests\Testimonial\TestimonialCreateRequest;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\Testimonial\TestimonialServiceInterface;
use App\Support\DataListManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    protected TestimonialServiceInterface $testimonial;

    public function __construct(TestimonialServiceInterface $testimonial)
    {
        $this->testimonial = $testimonial;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Testimonial List');
        if ($request->ajax()) {
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    return $this->testimonial
                        ->getDataTableData($request)['data']['data'];
                },
                columns: [
                    'image' => fn ($item) => $item->image
                    ? '<img src="' . $item->image . '" class="h-12 w-12 rounded object-cover">'
                    : '-',

                    'status_toggle' => fn ($item) =>
                    toggle_column(
                        route('testimonial.publish'),
                        $item->id,
                        $item->status === 1
                    ),

                    'actions' => function ($item) {
                        $buttons = [
                            edit_column(route('testimonial.edit', $item->id)),
                            delete_column(route('testimonial.delete', $item->id)),
                        ];

                        return action_buttons($buttons);
                    },
                ],
                rawColumns: ['image', 'status_toggle', 'actions']
            );
        }

        return ResponseService::send([
            'data' => $data,
        ], null, viewss('testimonial', 'list'));
    }

    public function create(Request $request)
    {
        $setup = $this->testimonial->createData($request)['data'];

        $data['pageTitle'] = __('Create Testimonial');
        $data['function_type'] = 'create';

        return ResponseService::send([
            'data' => $data,
        ], null, viewss('testimonial', 'create'));
    }

    public function store(TestimonialCreateRequest $request): RedirectResponse
    {
        $response = $this->testimonial->storeOrUpdate($request);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'testimonial.list');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $response = $this->testimonial->editData($id);
        if ($response['success'] === false) {
            return ResponseService::send();
        }

        $setup = $this->testimonial->createData(request())['data'];
        $item = $response['data'];

        $data['pageTitle'] = __('Update Testimonial');
        $data['function_type'] = 'update';
        $data['item'] = $item;

        return ResponseService::send([
            'data' => $data,
        ], null, viewss('testimonial', 'create'));
    }

    public function update(TestimonialCreateRequest $request, string $id): RedirectResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->testimonial->storeOrUpdate($request);

        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'testimonial.list');
    }

    public function destroy(string $id): RedirectResponse
    {
        $response = $this->testimonial->deleteData($id);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'testimonial.list');
    }

    public function testimonialStatus(Request $request): JsonResponse
    {
        try {
            $response = $this->testimonial->publish($request->id, $request->status);
            return response()->json($response);
        } catch (\Exception $e) {
            logStore('testimonialStatus', $e->getMessage());
            return response()->json(['success' => false, 'message' => somethingWrong()]);
        }
    }
}
