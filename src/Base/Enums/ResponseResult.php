<?php

namespace Kfn\Base\Enums;

use Kfn\Base\Contracts\IResponseResult;

enum ResponseResult implements IResponseResult
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
