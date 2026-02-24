<?php
namespace App\Support;

class FormContextResolver
{
    public static function resolve(): string
    {
        if (request()->isMethod('post')) {
            return request()->input('edit_id') ? 'update' : 'create';
        }

        if (request()->expectsJson()) {
            return 'api';
        }

        return 'create';
    }
}
