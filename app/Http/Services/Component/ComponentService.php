<?php

namespace App\Http\Services\Component;

use App\Http\Services\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ComponentService extends BaseService implements ComponentServiceInterface
{
    protected ComponentRepositoryInterface $componentRepository;

    public function __construct(ComponentRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->componentRepository = $repository;
    }

    public function getDataTableData(Request $request): array
    {
        $data = $this->componentRepository->componentList($request);
        return $this->sendResponse(true, __('Data retrieved successfully'), $data);
    }

    public function storeOrUpdateComponent(Request $request): array
    {
        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->slug ?? $request->name),
            'description' => $request->description ?? null,
            'status' => $request->status ?? true,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ];

        if ($request->edit_id) {
            $existingComponent = $this->componentRepository->find($request->edit_id);
            if (!$existingComponent) {
                return $this->sendResponse(false, __('Component not found'));
            }

            $data['slug'] = $existingComponent->slug === $data['slug']
                ? $data['slug']
                : Str::slug($data['slug']);

            $this->componentRepository->update($existingComponent->id, $data);
            $component = $this->componentRepository->find($existingComponent->id);
            return $this->sendResponse(true, __('Component updated successfully'), $component);
        } else {
            $component = $this->componentRepository->create($data);
            return $this->sendResponse(true, __('Component created successfully'), $component);
        }
    }

    public function deleteComponent(int $id): array
    {
        $component = $this->componentRepository->find($id);
        if (!$component) {
            return $this->sendResponse(false, __('Component not found'));
        }

        // Check if component is used in any page sections
        if ($component->pageSections()->count() > 0) {
            return $this->sendResponse(false, __('Cannot delete component that is in use'));
        }

        $this->componentRepository->delete($id);
        return $this->sendResponse(true, __('Component deleted successfully'));
    }

    public function publishComponent(int $id, bool $status): array
    {
        $component = $this->componentRepository->find($id);
        if (!$component) {
            return $this->sendResponse(false, __('Component not found'));
        }

        $this->componentRepository->updateStatus($id, $status);
        return $this->sendResponse(true, __('Component status updated successfully'));
    }

    public function getComponentWithFields(int $id): ?Model
    {
        return $this->componentRepository->find($id);
    }

    public function getActiveComponents(): Collection
    {
        return $this->componentRepository->getActiveComponents();
    }

    public function findBySlug(string $slug): ?Model
    {
        return $this->componentRepository->findBySlug($slug);
    }
}
