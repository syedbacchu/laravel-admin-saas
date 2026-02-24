<?php

namespace App\Http\Services\User;

use App\Enums\StatusEnum;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UserCreateRequest;
use App\Http\Services\BaseService;
use App\Http\Services\Response\DataService;
use App\Http\Services\Role\RoleServiceInterface;
use App\Support\Helpers;
use App\Support\SyncPermission;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserService extends BaseService implements UserServiceInterface
{
    use FileUploadTrait;

    protected UserRepositoryInterface $itemRepository;

    public function __construct(UserRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->itemRepository = $repository; // use this specifically
    }

    public function getListData($request): array
    {
        $data = $this->itemRepository->dataList($request);
        return $this->sendResponse(true,__('Data get successfully.'),$data);
    }

    public function storeOrUpdateData(UserCreateRequest $request): array
    {
        $item = "";
        $data = DataService::userCreateData($request);
        $message = "";
        if ($request->edit_id) {
            $item = $this->itemRepository->find($request->edit_id);
            if ($item) {
                $this->itemRepository->update($item->id,$data);
                $item = $this->itemRepository->find($item->id);
                SyncPermission::userCacheClear($item);
                $message = __('User updated successfully');
            } else {
                return $this->sendResponse(false,__('Data not found'));
            }
        } else {
            if(isset($request->type) && $request->type == "admin"){
                $data['added_by'] = Auth::user()->id();
            }
            $data['referral_code'] = uniqid().date('');
            $data['username'] = Helpers::generateUniqueUsername($request->name);
            $item = $this->itemRepository->create($data);
            $message = __('User created successfully');
        }

        return $this->sendResponse(true,$message,$item);
    }

    public function deleteData($id): array
    {
        $item = $this->itemRepository->find($id);
        if ($item) {
            $this->delete($item->id);
            return $this->sendResponse(true,__('Data deleted successfully'));
        } else {
            return $this->sendResponse(false,__('Data not found'));
        }
    }
    public function singleData($request): array {
        $value = $request->input('id')
            ?? $request->input('username')
            ?? $request->input('email')
            ?? $request->input('phone');

        if (!$value) {
            return $this->sendResponse(false, __('Invalid request parameter'));
        }

        $data = $this->itemRepository->getUserByAny($value);

        if (!$data) {
            return $this->sendResponse(false, __('User not found'));
        }

        return $this->sendResponse(true, __('Data get successfully'), $data);
    }

    public function statusUpdate($id,$status): array
    {
        $item = $this->itemRepository->find($id);
        if ($item) {
            $this->itemRepository->update($item->id,['status' => $status]);
            return $this->sendResponse(true,__('Status updated successfully'));
        } else {
            return $this->sendResponse(false,__('Data not found'));
        }
    }

    public function createData($request): array
    {
        $roleService = app(RoleServiceInterface::class);
        $request->merge(['status' => enum(StatusEnum::ACTIVE)]);
        $data['roles'] = $roleService->getDataTableData($request)['data']['data'];
        if($request->id) {
            $data['item'] = $this->itemRepository->find($request->id);
        }

        return $this->sendResponse(true,__('Data get successfully'),$data);
    }

    public function updateProfileProcess($request): array
    {
        $item = "";
        $data = DataService::userCreateData($request);
        $message = __('User updated successfully');
        $item = $this->itemRepository->find($request->user_id);
        if ($item) {
            $this->itemRepository->update($item->id, $data);
            $item = $this->itemRepository->find($item->id);
            SyncPermission::userCacheClear($item);
            if (Auth::id() == $request->user_id) {
                $message = __('Profile updated successfully');
            }
        } else {
            return $this->sendResponse(false,__('Data not found'));
        }
        return $this->sendResponse(true,$message,$item);
    }

    public function updatePasswordProcess(UpdatePasswordRequest $request): array
    {
        $user = Auth::user();
        if(! Hash::check($request->current_password, $user->password)) {
            return sendResponse(false, __("Old password is incorrect."),[],400);
        }
        if($request->current_password == $request->new_password) {
            return sendResponse(false, __("New password should not be same as old password."),[],400);
        }
        return $this->sendResponse(true,__('Password changed successfully'));
    }
}
