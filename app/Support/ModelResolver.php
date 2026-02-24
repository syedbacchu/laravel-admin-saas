<?php
namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ModelResolver
{
    public static function resolveFromRoute(): ?string
    {
        $route = request()->route();

        foreach ($route->parameters() as $param) {
            if ($param instanceof Model) {
                return get_class($param);
            }
        }

        // fallback: infer from controller
        $action = $route?->getActionName();

        if ($action) {
            [$controller] = explode('@', $action);
            $model = str_replace('Controller', '', class_basename($controller));
            $class = "App\\Models\\$model";

            if (class_exists($class)) {
                return $class;
            }
        }

        return null;
    }
}
