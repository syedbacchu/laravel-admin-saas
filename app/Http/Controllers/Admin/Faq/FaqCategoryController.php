<?php

namespace App\Http\Controllers\Admin\Faq;

use App\Http\Controllers\Controller;
use App\Http\Requests\Faq\FaqCategoryCreateRequest;
use App\Http\Services\FaqCategory\FaqCategoryServiceInterface;
use Illuminate\Http\Request;
use App\Http\Services\Response\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Yajra\DataTables\Facades\DataTables;

class FaqCategoryController extends Controller
{

    protected FaqCategoryServiceInterface $service;

    public function __construct(FaqCategoryServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data['pageTitle'] = __('Faq Category List');
        if ($request->ajax()) {
            return $this->getDataTableDataSet($request);
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('faqCategory', 'list'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $data['pageTitle'] = __('Create Faq Category');
        $data['function_type'] = 'create';
        $data['permissions'] = $this->service->faqCategoryCreateData($data)['data'];

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('faqCategory', path: 'create'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FaqCategoryCreateRequest $request): RedirectResponse
    {
        $response = $this->service->storeOrUpdateFaqCategory($request);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'faqCategory.list');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)

    {
        $data['pageTitle'] = __('Update Faq Category');
        $data['function_type'] = 'update';
        $response = $this->service->faqCategoryEditData($id);
        $data['item'] = $response['data'];
        return ResponseService::send([
            'data' => $data,
        ], view: viewss('faqCategory', path: 'create'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(FaqCategoryCreateRequest $request, string $id)
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeOrUpdateFaqCategory($request);

        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'faqCategory.list');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = $this->service->deleteFaqCategory($id);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'faqCategory.list');
    }

    protected function getDataTableDataSet($request): JsonResponse
    {
        $request->merge(['list_size' => 'datatable']);

        $result = $this->service->getDataTableData($request);
        $query  = $result['data']; // Builder

        return DataTables::eloquent($query)
            ->addColumn('status', function ($item) {
                return toggle_column(
                    route('faqCategory.publish'),
                    $item->id,
                    $item->status == 1
                );
            })
            ->addColumn('actions', function ($item) {
                return action_buttons([
                    edit_column(route('faqCategory.edit', $item->id)),
                    delete_column(route('faqCategory.delete', $item->id)),
                ]);
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }



    public function faqCategoryStatus(Request $request): JsonResponse
    {
        try {
            $response = $this->service->publishFaqCategory($request->id, $request->status);
            return response()->json($response);
        } catch (\Exception $e) {
            logStore('faqCategoryStatus', $e->getMessage());
            return response()->json(['success' => false, 'message' => somethingWrong()]);
        }
    }
}
