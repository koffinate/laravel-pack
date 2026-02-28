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

    public static function all(): array;

    public static function put(IResponseCode $responseCode, string|null $message = null): void;

    public static function get(string $key, int|string|null $default = null): int|string;

    public static function set(): void;

    public static function forget(): void;
}
