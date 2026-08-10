<?php

namespace App\Http\Services\Contact;

use App\Http\Repositories\BaseRepository;
use App\Models\Contact;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ContactRepository extends BaseRepository implements ContactRepositoryInterface
{
    public function __construct(Contact $model)
    {
        parent::__construct($model);
    }

    public function dataList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: Contact::query(),

            searchable: [
                'name',
                'email',
                'phone',
                'subject',
            ],

            filters: [
                'status' => [
                    'column' => 'status',
                ],
            ],

            select: [
                'id',
                'name',
                'email',
                'phone',
                'subject',
                'message',
                'status',
                'reply_message',
                'replied_at',
                'replied_by',
                'created_at',
            ],
        );
    }

    public function getPendingContacts()
    {
        return Contact::where('status', 'pending')->latest()->get();
    }

    public function getRepliedContacts()
    {
        return Contact::where('status', 'replied')->latest()->get();
    }

    public function getByEmail(string $email)
    {
        return Contact::where('email', $email)->latest()->get();
    }
}
