<?php

namespace Kfn\Base\Contracts;

interface IResponseCode
{
    /**
     * Determine httpCode from response code.
     *
     * @return int
     */
    public function httpCode(): int;

    /**
     * alias of httpCode.
     *
     * @return int
     */
    public function statusCode(): int;

    /**
     * Status text from httpCode.
     *
     * @return string
     */
    public function statusText(): string;

    /**
     * Set error to readable message string.
     *
     * @return string
     */
    public function message(): string;
}
