<?php

namespace Kfn\Base\Enums;

use Kfn\Base\Contracts\ResponseResultInterface;

enum ResponseResult implements ResponseResultInterface
{
    case DEFAULT;
    case CUSTOM;
    case SIMPLE;
    case SELECT2;

    public function dataWrapper(): string
    {
        return match ($this) {
            self::SELECT2 => 'results',
            default => config('koffinate.base.result.data_wrapper'),
        };
    }
}
