<?php

namespace LaravelInsight\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LaravelInsight\Traits\ArrayDriver;

class DiscoveredModel extends Model
{
    use ArrayDriver; // use sushi instead

    public function getRows()
    {
        $models = [];
        $processedFiles = [];
        $namespaces = ['App'];

        foreach ($namespaces as $namespace) {
            $relativePath = Str::after($namespace, 'App');
            // Use app_path() if relativePath is empty.
            $path = $relativePath ? app_path($relativePath) : app_path();
            if (!is_dir($path)) {
                continue;
            }

            // Only scan files directly in $path (not recursively).
            foreach (File::files($path) as $file) {
                $realPath = $file->getRealPath();
                if (!$realPath || in_array($realPath, $processedFiles)) {
                    continue;
                }
                $processedFiles[] = $realPath;

                $class = $namespace . '\\' . str_replace('.php', '', $file->getFilename());
                // Trigger autoload if needed.
                if (class_exists($class, false) && is_subclass_of($class, Model::class)) {
                    $models[] = ['class' => $class];
                }
            }
        }

        return $models;
    }
}
