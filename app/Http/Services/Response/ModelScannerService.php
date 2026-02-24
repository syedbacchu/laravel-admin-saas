<?php
namespace App\Http\Services\Response;

class ModelScannerService
{
    public static function getModels(array $exclude = [])
    {
        $models = [];
        $path = app_path('Models');

        foreach (scandir($path) as $file) {
            if ($file === '.' || $file === '..') continue;

            if (str_ends_with($file, '.php')) {
                $modelName = pathinfo($file, PATHINFO_FILENAME);

                if (in_array($modelName, $exclude)) continue;

                $models[] = "App\\Models\\$modelName";
            }
        }

        return $models;
    }

}
