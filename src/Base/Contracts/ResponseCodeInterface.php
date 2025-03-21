<?php

namespace Kfn\Base\Contracts;

interface ResponseCodeInterface
{
    /**
     * Determine httpCode from response code.
     *
     * @return int
     */
    public function httpCode(): int;

    /**
     * Set error to readable message string.
     *
     * @return string
     */
    public function message(): string;
}
