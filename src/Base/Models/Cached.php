<?php

namespace Kfn\Base\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Kfn\Database\Eloquent\HasModel;
use Kfn\Database\Eloquent\Model;

class Cached extends Model
{
    use HasUuids;

    /** {@inheritdoc} */
    protected $table = 'cached';

    /** {@inheritdoc} */
    public $timestamps = false;

    /** {@inheritdoc} */
    protected $guarded = [];

    /** {@inheritdoc} */
    protected $dateFormat = 'Y-m-d\TH:i:s.uP';

    /** @var bool */
    public static bool $catchEvents = true;

    /** {@inheritdoc} */
    protected static function booting(): void
    {
        parent::booting();

        if (static::$catchEvents) {
            static::saving(function (HasModel $model) {
                $expiration = $model->getAttribute('expiration');

                if (is_int($expiration) && empty($model->getAttribute('expires_at'))) {
                    $model->setAttribute('expires_at', now()->addSeconds($expiration)->toAtomString());
                }
            });

            static::creating(function (HasModel $model) {
                if (empty($model->getAttribute('created_at'))) {
                    $model->setAttribute('created_at', now()->toAtomString());
                }
            });

            static::updating(function (HasModel $model) {
                $model->setAttribute('renew', $model->getOriginal('renew') + 1);
            });
        }
    }

    /** {@inheritdoc} */
    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'expires_at' => 'immutable_datetime:atom',
            'created_at' => 'immutable_datetime:atom',
        ];
    }
}
