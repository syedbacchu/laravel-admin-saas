<?php

namespace App\Http\Services\Language;

use App\Http\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface LanguageRepositoryInterface extends BaseRepositoryInterface
{
    public function languageList(Request $request): array;
    public function createLanguage(array $data): Model;
}

