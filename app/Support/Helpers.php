<?php
namespace App\Support;


use App\Models\User;
use Illuminate\Support\Str;

class Helpers
{
    public static function generateUniqueUsername(string $name, $ignoreId = null): string
    {
        // Remove dot & spaces, lowercase
        $baseUsername = Str::lower(
            preg_replace('/[^a-zA-Z]/', '', $name)
        );

        $username = $baseUsername;
        $count = 1;

        while (
        User::where('username', $username)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $count++;
            $username = $baseUsername . $count;
        }

        return $username;
    }
}
