<?php

namespace App\Http\Controllers\Admin\Faq;

use App\Http\Controllers\Controller;
use App\Http\Requests\Faq\FaqCreateRequest;
use App\Http\Services\Faq\FaqServiceInterface;
use Illuminate\Http\Request;
use App\Http\Services\Response\ResponseService;
use App\Models\FaqCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Yajra\DataTables\Facades\DataTables;

class FaqController extends Controller
{
    protected FaqServiceInterface $service;

    public function __construct(FaqServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data['pageTitle'] = __('Faq List');
        if ($request->ajax()) {
            return $this->getDataTableDataSet($request);
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('faq', 'list'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $categories = FaqCategory::all();
        $data['pageTitle'] = __('Create Faq');
        $data['function_type'] = 'create';
        $data['categories'] = $categories;
        $data['permissions'] = $this->service->faqCreateData($data)['data'];

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('faq', path: 'create'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FaqCreateRequest $request): RedirectResponse
    {
        $response = $this->service->storeOrUpdateFaq($request);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'faq.list');
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
        $categories = FaqCategory::all();
        $data['pageTitle'] = __('Update Faq ');
        $data['function_type'] = 'update';
        $data['categories'] = $categories;
        $response = $this->service->faqEditData($id);
        $data['item'] = $response['data'];
        return ResponseService::send([
            'data' => $data,
        ], view: viewss('faq', path: 'create'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FaqCreateRequest $request, string $id)
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeOrUpdateFaq($request);

        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'faq.list');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = $this->service->deleteFaq($id);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'faq.list');
    }

    protected function getDataTableDataSet($request): JsonResponse
    {
        $request->merge(['list_size' => 'datatable']);

        $result = $this->service->getDataTableData($request);
        $query  = $result['data']; // Builder

        return DataTables::eloquent($query)
            ->addColumn('category_name', function ($item) {
                return $item->category ? $item->category->name : '-';
            })
            ->addColumn('status', function ($item) {
                return toggle_column(
                    route('faq.publish'),
                    $item->id,
                    $item->status == 1
                );
            })
            ->addColumn('actions', function ($item) {
                return action_buttons([
                    edit_column(route('faq.edit', $item->id)),
                    delete_column(route('faq.delete', $item->id)),
                ]);
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }
    public function faqStatus(Request $request): JsonResponse
    {
        try {
            $response = $this->service->publishFaq($request->id, $request->status);
            return response()->json($response);
        } catch (\Exception $e) {
            logStore('faqStatus', $e->getMessage());
            return response()->json(['success' => false, 'message' => somethingWrong()]);
        }
    }
}
