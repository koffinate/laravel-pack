<?php

namespace Kfn\Base\Contracts;

interface IResponseResult
{
    /**
     * Wrap response data.
     *
     * @return string
     */
    public function dataWrapper(): string;
}
