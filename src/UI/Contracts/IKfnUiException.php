<?php

namespace Kfn\UI\Contracts;

use Kfn\Base\Contracts\IResponseCode;

interface IKfnUiException
{
    public function exist(): bool;

    public function getResponseCode(): IResponseCode|null;

    public function getName(): string|null;

    public function getMessage(): string|null;

    public function getCode(): int|null;

    public function getStatusText(): string|null;

    public function toArray(): array;
}
