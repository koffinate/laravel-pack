<?php

namespace Kfn\UI;

use Illuminate\Support\Fluent;
use Kfn\Base\Contracts\IResponseCode;
use Kfn\UI\Contracts\IKfnUiException;

class KfnUiException implements IKfnUiException
{
    private static IResponseCode|null $responseCode = null;
    private static string|null $name = null;
    private static string|null $message = null;
    private static int|null $code = null;
    private static string|null $statusText = null;

    public function __construct(array|null $data = null)
    {
        $exceptionData = new Fluent($data ?? ((array) session('kfn-exception') ?? []));
        try {
            self::$responseCode = $exceptionData->get('rc')::tryFrom($exceptionData->get('name') ?? null);
            if (self::$responseCode instanceof IResponseCode) {
                self::$name = self::$responseCode->name;
                self::$message = $exceptionData->get('message') ?? self::$responseCode->message();
                self::$code = (int) ($exceptionData->get('statusCode') ?? self::$responseCode->statusCode());
                self::$statusText = $exceptionData->get('statusText') ?? self::$responseCode->statusText();
            }
        } catch (\Throwable $throw) {
            self::$responseCode = null;
            self::$name = null;
        }
    }

    public static function of(array $data): static
    {
        return new static($data);
    }

    public function exist(): bool
    {
        return 'redirect' === config('koffinate.ui.exception.handling_method')
            && self::$responseCode instanceof IResponseCode;
    }

    public function getResponseCode(): IResponseCode|null
    {
        return self::$responseCode;
    }

    public function getName(): string|null
    {
        return self::$name;
    }

    public function getMessage(): string|null
    {
        return self::$message;
    }

    public function getCode(): int|null
    {
        return self::$code;
    }

    public function getStatusText(): string|null
    {
        return self::$statusText;
    }

    public function toArray(): array
    {
        return [
            'source' => self::$responseCode ? self::$responseCode::class : 'unknown',
            'name' => self::$name,
            'status-code' => self::$code,
            'status-text' => self::$statusText,
            'message' => self::$message,
        ];
    }

    // public function __invoke()
    // {
    //     static::of(session('kfn-exception') ?? []);
    // }
}
