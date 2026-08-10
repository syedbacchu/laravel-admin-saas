<?php

namespace App\Http\Services\Subscriber;

use App\Http\Services\BaseService;
use App\Models\Subscriber;
use Exception;
use Illuminate\Http\Request;

class SubscriberService extends BaseService implements SubscriberServiceInterface
{
    protected SubscriberRepositoryInterface $subscriberRepository;

    public function __construct(SubscriberRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->subscriberRepository = $repository;
    }

    public function getDataTableData($request): array
    {
        $data = $this->subscriberRepository->dataList($request);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function subscribe(array $data): array
    {
        try {
            // Check if email already exists
            $existingSubscriber = $this->subscriberRepository->getByEmail($data['email']);

            if ($existingSubscriber) {
                // If exists but inactive, reactivate
                if ($existingSubscriber->status === 0) {
                    $this->subscriberRepository->update($existingSubscriber->id, ['status' => 1]);
                    return $this->sendResponse(true, 'Subscription reactivated successfully', $existingSubscriber->fresh()->toArray(), 200);
                }

                // Already subscribed and active
                return $this->sendResponse(false, 'Email already subscribed', [], 409);
            }

            // Create new subscriber
            $subscriber = $this->subscriberRepository->create([
                'email' => $data['email'],
                'status' => 1,
            ]);

            return $this->sendResponse(true, 'Successfully subscribed', $subscriber->toArray(), 201);
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Failed to subscribe', [], 500, $e->getMessage());
        }
    }

    public function toggleStatus(int $id): array
    {
        try {
            $subscriber = $this->subscriberRepository->find($id);
            if (!$subscriber) {
                return $this->sendResponse(false, 'Subscriber not found', [], 404);
            }

            $newStatus = $subscriber->status === 1 ? 0 : 1;
            $this->subscriberRepository->update($id, ['status' => $newStatus]);

            $message = $newStatus === 1 ? 'Subscriber activated' : 'Subscriber deactivated';
            return $this->sendResponse(true, $message, $subscriber->fresh()->toArray());
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Failed to update status', [], 500, $e->getMessage());
        }
    }
}
