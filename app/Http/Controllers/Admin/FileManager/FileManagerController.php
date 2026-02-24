<?php

namespace App\Http\Controllers\Admin\FileManager;

use App\Enums\FileDestinationEnum;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Support\FileManager;
use App\Http\Services\Response\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FileManagerController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $data['pageTitle'] = __('File Manager');
        $data['items'] = FileManager::list($request);

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('file','list'));
    }

    public function create()
    {
        $data['pageTitle'] = __('Upload File');


        return ResponseService::send([
            'data' => $data,
        ], view: viewss('file','create'));
    }

    public function store(Request $request): RedirectResponse {
        if ($request->photo) {
            $response = FileManager::uploadFileStorage($request->photo,enum(FileDestinationEnum::GENERAL_IMAGE_PATH));
            return ResponseService::send([
                'response' => $response,
            ], successRoute: 'fileManager.list');
        } else {
            return ResponseService::send([
                'response' => ['message' => __('File upload failed')],
            ], successRoute: 'fileManager.list');
        }
    }


    public function destroy($id): RedirectResponse {
        $response = FileManager::deleteFile($id);
        return ResponseService::send([
            'response' => $response,
        ]);
    }

    public function list(Request $request): View|JsonResponse
    {
        $data['pageTitle'] = __('File Manager');
        $user = Auth::user();
        if ($user->role_module != enum(UserRole::SUPER_ADMIN_ROLE)) {
            $request->merge(['user_id' => $user->id]);
        }
        $data['items'] = FileManager::list($request);

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('file','list_data'));
    }

    public function storeFile(Request $request) {
        if ($request->photo) {
            $response = FileManager::uploadFileStorage($request->photo,enum(FileDestinationEnum::GENERAL_IMAGE_PATH));
            return $response;
        } else {
            return sendResponse(false, __('File upload failed'),[],400);
        }
    }

    public function listPartial(Request $request)
    {
        $data['pageTitle'] = __('File Manager');
        $user = Auth::user();
        if ($user->role_module != enum(UserRole::SUPER_ADMIN_ROLE)) {
            $request->merge(['user_id' => $user->id]);
        }
        $data['items'] = FileManager::list($request);
        return view(viewss('file', 'partial_data'), $data)->render();
    }

}
