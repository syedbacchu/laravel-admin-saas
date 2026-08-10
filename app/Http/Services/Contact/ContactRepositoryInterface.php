<?php

namespace App\Http\Services\Contact;

use App\Http\Repositories\BaseRepositoryInterface;
use Illuminate\Http\Request;

interface ContactRepositoryInterface extends BaseRepositoryInterface
{
    public function dataList(Request $request): array;
    public function getPendingContacts();
    public function getRepliedContacts();
    public function getByEmail(string $email);
}
