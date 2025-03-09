<?php

namespace LaravelInsight\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelInsight\Traits\ArrayDriver;

class DiscoveredModel extends Model
{
    use ArrayDriver;

    protected $rows = [];

    /**
     * Instantiate the model with the given records.
     */
    public static function fromArray(array $rows): self
    {
        $instance = new static();
        $instance->rows = $rows;
        static::bootArrayDriver();
        return $instance;
    }

}
