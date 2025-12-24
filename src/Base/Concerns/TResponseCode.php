<?php

namespace Kfn\Base\Concerns;

use Symfony\Component\HttpFoundation\Response;

trait TResponseCode
{
    /**
     * alias of httpCode.
     *
     * @return int
     */
    public function statusCode(): int
    {
        return $this->httpCode();
    }

    /**
     * Status text from httpCode.
     *
     * @return string
     */
    public function statusText(): string
    {
        return Response::$statusTexts[$this->httpCode()] ?? 'Unknown Error';
    }

    /**
     * Set error to readable message string.
     *
     * @return string
     */
    public function message(): string
    {
        return ucwords(strtolower(str_replace(['ERR_', '_'], ['', ' '], $this->name)));
    }
}
