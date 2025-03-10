<?php

namespace LaravelInsight\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LaravelInsight\Traits\ArrayDriver;

class DiscoveredModel extends Model
{
    use ArrayDriver; // use sushi instead

    public function getRows(): array
    {
        $models = [];
        $processedFiles = [];
        $namespaces = ['App'];

        foreach ($namespaces as $namespace) {
            // Determine the path for the namespace.
            $relativePath = Str::after($namespace, 'App');
            $path = $relativePath ? app_path($relativePath) : app_path();
            if (!is_dir($path)) {
                continue;
            }

            // Scan only the files directly in $path (non-recursively)
            foreach (File::files($path) as $file) {
                // Only process PHP files.
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $realPath = $file->getRealPath();
                if (!$realPath || in_array($realPath, $processedFiles)) {
                    continue;
                }
                $processedFiles[] = $realPath;

                // Parse the file to extract the fully qualified class name.
                $class = self::getClassFromFile($realPath);
                // If we found a class and the file declares it extends Model, add it.
                if ($class && self::fileExtendsModel($realPath)) {
                    $models[] = ['class' => $class];
                }
            }
        }

        return $models;
    }

    /**
     * Extract the fully qualified class name from a PHP file.
     */
    protected static function getClassFromFile(string $file): ?string
    {
        $contents = file_get_contents($file);
        $tokens = token_get_all($contents);
        $namespace = '';
        $class = '';
        $count = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];
            if (!is_array($token)) {
                continue;
            }
            if ($token[0] === T_NAMESPACE) {
                $namespace = '';
                $i++;
                // Collect namespace parts (T_STRING and T_NS_SEPARATOR)
                while ($i < $count && is_array($tokens[$i]) &&
                    in_array($tokens[$i][0], [T_STRING, T_NS_SEPARATOR])) {
                    $namespace .= $tokens[$i][1];
                    $i++;
                }
            }
            if ($token[0] === T_CLASS) {
                // Skip whitespace tokens.
                $i++;
                while ($i < $count && is_array($tokens[$i]) && $tokens[$i][0] === T_WHITESPACE) {
                    $i++;
                }
                if ($i < $count && is_array($tokens[$i]) && $tokens[$i][0] === T_STRING) {
                    $class = $tokens[$i][1];
                    break;
                }
            }
        }
        if ($class !== '') {
            return $namespace ? $namespace . '\\' . $class : $class;
        }

        return null;
    }

    /**
     * Checks if the file declares a class that extends Model.
     */
    protected static function fileExtendsModel(string $file): bool
    {
        $contents = file_get_contents($file);
        $tokens = token_get_all($contents);
        $foundClass = false;
        $extends = '';
        $count = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];
            if (is_array($token)) {
                if ($token[0] === T_CLASS) {
                    $foundClass = true;
                }
                if ($foundClass && $token[0] === T_EXTENDS) {
                    $i++;
                    // Skip whitespace and comments.
                    while ($i < $count) {
                        $current = $tokens[$i];
                        if (is_array($current)) {
                            if (in_array($current[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT])) {
                                $i++;

                                continue;
                            }
                            if (in_array($current[0], [T_STRING, T_NS_SEPARATOR])) {
                                $extends .= $current[1];
                            } else {
                                break;
                            }
                        } else {
                            break;
                        }
                        $i++;
                    }
                    break;
                }
            }
        }
        if ($extends) {
            $extends = ltrim($extends, '\\');

            return $extends === 'Model' || $extends === 'Illuminate\Database\Eloquent\Model';
        }

        return false;
    }
}
