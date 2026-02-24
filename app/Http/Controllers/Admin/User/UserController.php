<?php

namespace App\Http\Controllers\Admin\User;

use App\Enums\StatusEnum;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Requests\User\UserCreateRequest;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\User\UserServiceInterface;
use App\Support\DataListManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    protected UserServiceInterface $service;

    public function __construct(UserServiceInterface $service)
    {
        $this->service = $service;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data['pageTitle'] = __('User List');
        if ($request->ajax()) {
            $notIn = [
                'id' => [Auth::id()],
                'role_module' => [enum(UserRole::SUPER_ADMIN_ROLE)]
            ];
            $request->merge(['notIn' => $notIn]);
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    return $this->service
                        ->getListData($request)['data']['data'];
                },
                columns: [
                    'name' => function ($item) {
                        return '
                        <div class="flex items-center gap-2">
                            <img src="'.userImage($item->image).'" class="w-8 h-8 rounded-full">
                            <p>'.$item->name.'<br>
                            <small>'.$item->username.'</small>
                            </p>

                        </div>';
                    },

                    'created_at' => fn ($item) =>
                    $item->created_at?->diffForHumans(),
                    'role_module' => fn ($item) =>
                    UserRole::label($item->role_module),
                    'phone' => fn ($item) =>
                    $item->phone . '<br>' .$item->email,

                    'status' => fn ($item) =>
                    toggle_column(
                        route('user.status'),
                        $item->id,
                        $item->status == 1
                    ),

                    'actions' => fn ($item) =>
                    action_buttons([
                        edit_column(route('user.edit', $item->id)),
                        delete_column(route('user.delete', $item->id)),
                    ]),
                ],
                rawColumns: ['name','phone', 'status', 'actions']
            );
        }


        return ResponseService::send([
            'data' => $data,
        ], view: viewss('user','list'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $request->merge(['guard' => 'web']);
        $data = $this->service->createData($request)['data'];
        $data['pageTitle'] = __('Create New User');

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('user','create'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserCreateRequest $request)
    {
        $response = $this->service->storeOrUpdateData($request);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'user.list');
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
    public function edit(Request $request,string $id)
    {
        $request->merge(['guard' => 'web', 'id' => $id]);
        $data = $this->service->createData($request)['data'];
        $data['pageTitle'] = __('Update User');

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('user','create'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = $this->service->deleteData($id);
        return ResponseService::send([
            'response' => $response,
        ]);
    }

    public function status(Request $request): JsonResponse {
        try {
            $response = $this->service->statusUpdate($request->id,$request->status);
            return response()->json($response);
        } catch (\Exception $e) {
            logStore('user Status',$e->getMessage());
            return response()->json(['success'=>false,'message'=>somethingWrong()]);
        }
    }

    public function profile(Request $request) {
        $request->merge(['id' => Auth::id()]);
        $response = $this->service->singleData($request);
        $response['pageTitle'] = __('Profile');
        return ResponseService::send([
            'data' => $response, 'response' => $response
        ],
            view: viewss('user', 'profile')
        );
    }

    public function editProfile(Request $request) {
        $request->merge(['id' => Auth::id()]);
        $response = $this->service->singleData($request);
        $response['pageTitle'] = __('Update Profile');
        return ResponseService::send([
            'data' => $response, 'response' => $response
        ],
            view: viewss('user', 'edit')
        );
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $request->merge(['user_id' => Auth::id()]);
        $response = $this->service->updateProfileProcess($request);
        return ResponseService::send([
            'response' => $response,
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $response = $this->service->updatePasswordProcess($request);
        return ResponseService::send([
            'response' => $response,
        ]);
    }

}
