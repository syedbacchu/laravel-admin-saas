<?php

use App\Http\Services\Response\Viewed;
use Illuminate\Support\Facades\Log;
use App\Support\Settings;
use Illuminate\Support\Str;

function logStore($type, $text = '', $timestamp = true): void
{
    if(gettype($text) == 'array'){
        $text = json_encode($text);
    }
    if ($timestamp) {
        $datetime = date("d-m-Y H:i:s");
        $text = "$datetime, $type: $text \r\n\r\n";
    } else {
        $text = "$type\r\n\r\n";
    }
    Log::info($text);
}

function somethingWrong($text=null) {
    return isset($text) ? $text : __('Something went wrong');
}


function viewss($type,$path) {
    return Viewed::get($type,$path);
}

function sendResponse(
    bool $success,
    string $message = "Invalid request",
    $data = [],
    int $status = 200,
    string $errorMessage = ""
    ) {
        return [
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'status' => $status,
            'error_message' => $errorMessage,
        ];
    }

function sendApiResponse(
    bool $success,
    string $message = "Invalid request",
    $data = [],
    int $status = 200,
    string $errorMessage = "")
    {
        return response()->json([
            'success'       => $success ?? false,
            'message'       => $message ?? '',
            'data'          => $data ?? [],
            'status'        => $status ?? 200,
            'error_message' => $errorMessage ?? ($success ? '' : ($message ?? 'Error')),
        ], $status ?? 200);
    }

/**
 * @param int $a
 * @return string
 */
// random number
function randomNumber($a = 10)
{
    $x = '0123456789';
    $c = strlen($x) - 1;
    $z = '';
    for ($i = 0; $i < $a; $i++) {
        $y = rand(0, $c);
        $z .= substr($x, $y, 1);
    }
    return $z;
}

function settings(string $key = null, $default = null):mixed
{
    if ($key === null) {
        return Settings::all();
    }

    return Settings::get($key, $default);
}

function enum($enum): mixed
{
    return $enum->value;
}

function uploadImageFileInStorage($reqFile,$path,$oldImage = null){
    $service = new \Sdtech\FileUploaderLaravel\Service\FileUploadLaravelService();
    $response = $service->uploadImageInStorage($reqFile,$path,$oldImage);
    return $response;
}

function formatPermissionName(string $input): string
{
    return (string) Str::of($input)
        ->replaceMatches('/([a-z])([A-Z])/', '$1 $2')
        ->replace(['.', '_', '-'], ' ')
        ->replaceMatches('/\s+/', ' ')
        ->trim()
        ->title();
}


function make_unique_slug($title, $table_name = NULL, $column_name = 'slug')
{
    $table = array(
        'Š' => 'S', 'š' => 's', 'Đ' => 'Dj', 'đ' => 'dj', 'Ž' => 'Z', 'ž' => 'z', 'Č' => 'C', 'č' => 'c', 'Ć' => 'C', 'ć' => 'c',
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
        'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O',
        'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss',
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e',
        'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o',
        'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'ý' => 'y', 'þ' => 'b',
        'ÿ' => 'y', 'Ŕ' => 'R', 'ŕ' => 'r', '/' => '-', ' ' => '-'
    );

    // -- Remove duplicated spaces
    $stripped = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $title);

    // -- Returns the slug
    $slug = strtolower(strtr($title, $table));
    $slug = str_replace("?", "", $slug);
    if (isset($table_name)) {
        $item = DB::table($table_name)->where($column_name, $slug)->first();
        if (isset($item)) {
            $slug = setSlugAttribute($slug, $table_name, $column_name);
        }
    }

    return $slug;
}

function setSlugAttribute($value, $table, $column_name = 'slug')
{
    if (DB::table($table)->where($column_name, $value)->exists()) {
        return incrementSlug($value, $table, $column_name);
    }
    return $value;
}

function incrementSlug($slug, $table, $column_name = 'slug')
{
    $original = $slug;
    $count = 2;

    while (DB::table($table)->where($column_name, $slug)->exists()) {
        $slug = "{$original}-" . $count++;
    }

    return $slug;
}

function userImage($image=null)
{
    $default = asset('assets/images/avatar.png');
    return $image ? $image : $default;
}

function canAccess(?string $permission): bool
{
    if (!$permission) {
        return true;
    }

    $user = auth()->user();

    if (!$user) {
        return false;
    }

    if ($user->role_module == enum(\App\Enums\UserRole::SUPER_ADMIN_ROLE)) {
        return true;
    }

    return $user->hasPermission($permission);
}
