<?php

namespace Kfn\Base\Contracts;

interface ResponseResultInterface
{
    /**
     * Wrap response data
     *
     * @return string
     */
    public function dataWrapper(): string;
}
