<?php

namespace Kfn\Database\Eloquent;

use Illuminate\Database\Eloquent\Builder as BaseBuilder;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

abstract class Model extends BaseModel implements HasModel
{
    /** @inheritdoc */
    protected static string $builder = Builder::class;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (property_exists($this, 'appendsMore')) {
            $this->appends = array_merge($this->appendsMore, $this->appends);
        }

        if (property_exists($this, 'extraFillable')) {
            $this->mergeFillable((array) $this->extraFillable);
        }
    }

    /**
     * @return BaseBuilder
     */
    public static function cleanQuery(): BaseBuilder
    {
        $instance = new static;

        return $instance->registerGlobalScopes($instance->newModelQuery());
    }

    /**
     * @return QueryBuilder
     */
    public static function rawQuery(): QueryBuilder
    {
        $instance = new static();

        return DB::connection($instance->getConnectionName())->table($instance->getTable());
    }
}
