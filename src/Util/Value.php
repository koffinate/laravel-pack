<?php

namespace Kfn\Util;

class Value
{
    /**
     * @param  mixed  $value
     *
     * @return bool
     */
    public static function isTrue(mixed $value): bool
    {
        return in_array(static::preferLowercase($value), [1, '1', true, 'true', 'on']);
    }

    /**
     * @param  mixed  $value
     *
     * @return bool
     */
    public static function isFalse(mixed $value): bool
    {
        return in_array(static::preferLowercase($value), [0, '0', false, 'false', 'off']);
    }

    /**
     * @param  mixed  $value
     *
     * @return string
     */
    public static function shouldLowercase(mixed $value): string
    {
        return strtolower((string) $value);
    }

    /**
     * @param  mixed  $value
     *
     * @return string
     */
    public static function shouldUppercase(mixed $value): string
    {
        return strtoupper((string) $value);
    }

    /**
     * @param  mixed  $value
     *
     * @return mixed
     */
    public static function preferLowercase(mixed $value): mixed
    {
        return match (true) {
            is_string($value) => strtolower($value),
            is_object($value) => strtolower((string) $value),
            default => $value,
        };
    }

    /**
     * @param  mixed  $value
     *
     * @return mixed
     */
    public static function preferUppercase(mixed $value): mixed
    {
        return match (true) {
            is_string($value) => strtoupper($value),
            is_object($value) => strtoupper((string) $value),
            default => $value,
        };
    }
}
