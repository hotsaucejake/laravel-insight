<?php

namespace LaravelInsight\Traits;

use Closure;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\SQLiteBuilder;
use Illuminate\Support\Str;

trait ArrayDriver
{
    protected static $arrayDriverConnection;

    public function getRows()
    {
        return $this->rows;
    }

    public function getSchema()
    {
        return $this->schema ?? [];
    }

    protected function arrayDriverCacheReferencePath()
    {
        return (new \ReflectionClass(static::class))->getFileName();
    }

    protected function arrayDriverShouldCache()
    {
        return property_exists(static::class, 'rows');
    }

    public static function resolveConnection($connection = null)
    {
        return static::$arrayDriverConnection;
    }

    protected function arrayDriverCachePath()
    {
        return implode(DIRECTORY_SEPARATOR, [
            $this->arrayDriverCacheDirectory(),
            $this->arrayDriverCacheFileName(),
        ]);
    }

    protected function arrayDriverCacheFileName()
    {
        return 'arrayDriver-' . Str::kebab(str_replace('\\', '', static::class)) . '.sqlite';
    }

    protected function arrayDriverCacheDirectory()
    {
        return realpath(storage_path('framework/cache'));
    }

    public static function bootArrayDriver()
    {
        $instance = (new static);

        $cachePath = $instance->arrayDriverCachePath();
        $dataPath = $instance->arrayDriverCacheReferencePath();

        $states = [
            'cache-file-found-and-up-to-date' => function () use ($cachePath) {
                static::setSqliteConnection($cachePath);
            },
            'cache-file-not-found-or-stale' => function () use ($cachePath, $dataPath, $instance) {
                static::cacheFileNotFoundOrStale($cachePath, $dataPath, $instance);
            },
            'no-caching-capabilities' => function () use ($instance) {
                static::setSqliteConnection(':memory:');

                $instance->migrate();
            },
        ];

        switch (true) {
            case !$instance->arrayDriverShouldCache():
                $states['no-caching-capabilities']();
                break;

            case file_exists($cachePath) && filemtime($dataPath) <= filemtime($cachePath):
                $states['cache-file-found-and-up-to-date']();
                break;

            case file_exists($instance->arrayDriverCacheDirectory()) && is_writable($instance->arrayDriverCacheDirectory()):
                $states['cache-file-not-found-or-stale']();
                break;

            default:
                $states['no-caching-capabilities']();
                break;
        }
    }

    protected static function cacheFileNotFoundOrStale($cachePath, $dataPath, $instance)
    {
        file_put_contents($cachePath, '');

        static::setSqliteConnection($cachePath);

        $instance->migrate();

        touch($cachePath, filemtime($dataPath));
    }

    protected function newRelatedInstance($class)
    {
        return tap(new $class, function ($instance) {
            if (!$instance->getConnectionName()) {
                $instance->setConnection($this->getConnectionResolver()->getDefaultConnection());
            }
        });
    }

    protected static function setSqliteConnection($database)
    {
        $config = [
            'driver' => 'sqlite',
            'database' => $database,
        ];

        static::$arrayDriverConnection = app(ConnectionFactory::class)->make($config);

        app('config')->set('database.connections.' . static::class, $config);
    }

    public function migrate()
    {
        $rows = $this->getRows();
        $tableName = $this->getTable();

        if (count($rows)) {
            $this->createTable($tableName, $rows[0]);
        } else {
            $this->createTableWithNoData($tableName);
        }

        foreach (array_chunk($rows, $this->getarrayDriverInsertChunkSize()) ?? [] as $inserts) {
            if (!empty($inserts)) {
                static::insert($inserts);
            }
        }
    }

    public function createTable(string $tableName, $firstRow)
    {
        $this->createTableSafely($tableName, function ($table) use ($firstRow) {
            // Add the "id" column if it doesn't already exist in the rows.
            if ($this->incrementing && !array_key_exists($this->primaryKey, $firstRow)) {
                $table->increments($this->primaryKey);
            }

            foreach ($firstRow as $column => $value) {
                switch (true) {
                    case is_int($value):
                        $type = 'integer';
                        break;
                    case is_numeric($value):
                        $type = 'float';
                        break;
                    case is_string($value):
                        $type = 'string';
                        break;
                    case is_object($value) && $value instanceof \DateTime:
                        $type = 'dateTime';
                        break;
                    default:
                        $type = 'string';
                }

                if ($column === $this->primaryKey && $type == 'integer') {
                    $table->increments($this->primaryKey);

                    continue;
                }

                $schema = $this->getSchema();

                $type = $schema[$column] ?? $type;

                $table->{$type}($column)->nullable();
            }

            if ($this->usesTimestamps() && (!in_array('updated_at', array_keys($firstRow)) || !in_array('created_at', array_keys($firstRow)))) {
                $table->timestamps();
            }

            $this->afterMigrate($table);
        });
    }

    protected function afterMigrate(BluePrint $table) {}

    public function createTableWithNoData(string $tableName): void
    {
        $this->createTableSafely($tableName, function ($table) {
            $schema = $this->getSchema();

            if ($this->incrementing && !in_array($this->primaryKey, array_keys($schema))) {
                $table->increments($this->primaryKey);
            }

            foreach ($schema as $name => $type) {
                if ($name === $this->primaryKey && $type == 'integer') {
                    $table->increments($this->primaryKey);

                    continue;
                }

                $table->{$type}($name)->nullable();
            }

            if ($this->usesTimestamps() && (!in_array('updated_at', array_keys($schema)) || !in_array('created_at', array_keys($schema)))) {
                $table->timestamps();
            }
        });
    }

    protected function createTableSafely(string $tableName, Closure $callback): void
    {
        /** @var SQLiteBuilder $schemaBuilder */
        $schemaBuilder = static::resolveConnection()->getSchemaBuilder();

        try {
            $schemaBuilder->create($tableName, $callback);
        } catch (QueryException $e) {
            if (Str::contains($e->getMessage(), [
                'already exists (SQL: create table',
                sprintf('table "%s" already exists', $tableName),
            ])) {
                // This error can happen in rare circumstances due to a race condition.
                // Concurrent requests may both see the necessary preconditions for
                // the table creation, but only one can actually succeed.
                return;
            }

            throw $e;
        }
    }

    public function usesTimestamps(): bool
    {
        // Override the Laravel default value of $timestamps = true; Unless otherwise set.
        return (new \ReflectionClass($this))->getProperty('timestamps')->class === static::class
            ? parent::usesTimestamps()
            : false;
    }

    public function getarrayDriverInsertChunkSize()
    {
        return $this->arrayDriverInsertChunkSize ?? 100;
    }

    public function getConnectionName()
    {
        return static::class;
    }
}
