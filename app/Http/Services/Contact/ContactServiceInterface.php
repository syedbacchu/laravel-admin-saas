<?php

namespace App\Http\Services\Contact;

use App\Http\Services\BaseServiceInterface;

interface ContactServiceInterface extends BaseServiceInterface
{
    public function submitContact(array $data): array;
    public function getContactList(array $filters = []): array;
    public function replyToContact(int $id, array $data): array;
    public function getContactDetail(int $id): array;
}
