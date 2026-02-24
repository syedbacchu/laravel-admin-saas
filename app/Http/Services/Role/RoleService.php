<?php

namespace App\Http\Services\Role;

use App\Enums\StatusEnum;
use App\Enums\UploadFolderEnum;
use App\Http\Requests\Role\RoleCreateRequest;
use App\Http\Requests\Slider\SliderCreateRequest;
use App\Http\Services\BaseService;
use App\Support\SyncPermission;
use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\Builder;

class RoleService extends BaseService implements RoleServiceInterface
{
    protected RoleRepositoryInterface $itemRepository;

    public function __construct(RoleRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->itemRepository = $repository; // use this specifically
    }

    public function getDataTableData($request): array
    {
        $data = $this->itemRepository->dataList($request);
        return $this->sendResponse(true,__('Data get successfully.'),$data);
    }

    public function storeOrUpdateData(RoleCreateRequest $request): array
    {
        $item = "";
        $data = [
            'name' => $request->name,
            'guard' => $request->guard
        ];
        $message = "";
        if ($request->edit_id) {
            $item = $this->itemRepository->find($request->edit_id);
            if ($item) {
                $this->itemRepository->update($item->id,$data);
                $item->permissions()->sync($request->permissions);
                $item = $this->itemRepository->find($item->id);
                SyncPermission::roleCacheClear($item);
                $message = __('Role updated successfully');
            } else {
                return $this->sendResponse(false,__('Data not found'));
            }
        } else {
            $data['slug'] = make_unique_slug($request->name, 'roles');
            $item = $this->itemRepository->create($data);
            $item->permissions()->sync($request->permissions);
            $message = __('Role created successfully');
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

    public function permissionDelete($id): array
    {
        $item = $this->itemRepository->getPermission($id);
        if ($item) {
            $this->itemRepository->deletePermission($item->id);
            return $this->sendResponse(true,__('Data deleted successfully'));
        } else {
            return $this->sendResponse(false,__('Data not found'));
        }
    }

     public function publishPermission($id,$status): array
     {
        $item = $this->itemRepository->getPermission($id);
        if ($item) {
            $this->itemRepository->updatePermission($item->id,['status' => $status]);
            return $this->sendResponse(true,__('Status updated successfully'));
        } else {
            return $this->sendResponse(false,__('Data not found'));
        }
     }

    public function getPermissionList($request) {
        $responseData = $this->itemRepository->permissionList($request);
        return $this->sendResponse(true,__('Data get successfully'),$responseData );
    }
    public function getSinglePermission($id): array {
        return $this->sendResponse(true,__('Data get successfully'),$this->itemRepository->getPermission($id) );
    }

    public function statusRole($id,$status): array
    {
        $item = $this->itemRepository->find($id);
        if ($item) {
            $this->itemRepository->update($item->id,['status' => $status]);
            return $this->sendResponse(true,__('Status updated successfully'));
        } else {
            return $this->sendResponse(false,__('Data not found'));
        }
    }

    public function roleCreateData($guard): array
    {
        $data = $this->itemRepository->getModulePermissions($guard);

        return $this->sendResponse(true,__('Data get successfully'),$data);
    }

    public function roleEditData($id): array {
        $data['item'] = $this->itemRepository->find($id);
        if ($data['item']) {
            $data['permissions'] = $this->itemRepository->getModulePermissions($data['item']->guard);
            return $this->sendResponse(true,__('Data get successfully'), $data);
        } else {
            return $this->sendResponse(false,__('Data not found'));
        }
    }
}
