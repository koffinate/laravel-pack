<?php

namespace Kfn\Database\Eloquent;

use Illuminate\Database\Eloquent\Builder as BaseBuilder;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

abstract class Model extends BaseModel implements HasModel
{
    /** {@inheritdoc} */
    protected static string $builder = Builder::class;

    /**
     * @param  array  $attributes
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
     * @param  array  $attributes
     *
     * @return static
     */
    public static function self(array $attributes = []): static
    {
        return new static($attributes);
    }

    /**
     * @param  array|object  $data
     *
     * @return array
     * @throws \Throwable
     */
    public static function toFillable(array|object $data): array
    {
        $fillable = static::self()->getFillable();

        if (is_object($data)) {
            $data = fluent($data)->toArray();
        }

        return Arr::only($data, Arr::flatten($fillable));
    }

    /**
     * @return string
     */
    public static function tableName(): string
    {
        return static::self()->getTable();
    }

    /**
     * @return BaseBuilder
     */
    public static function cleanQuery(): BaseBuilder
    {
        $instance = static::self();

        return $instance->registerGlobalScopes($instance->newModelQuery());
    }

    /**
     * @return QueryBuilder
     */
    public static function rawQuery(): QueryBuilder
    {
        $instance = static::self();

        return DB::connection($instance->getConnectionName())->table($instance->getTable());
    }
}
