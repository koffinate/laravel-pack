<?php

namespace Kfn\UI;

use Illuminate\Support\Fluent;
use Kfn\Base\Contracts\IResponseCode;
use Kfn\UI\Contracts\IKfnUiException;

class KfnUiException implements IKfnUiException
{
    public static IResponseCode|null $responseCode = null;
    public static string|null $name = null;
    public static string|null $message = null;
    public static int|null $code = null;
    public static string|null $statusText = null;

    // public function __construct(
    //     IResponseCode|null $responseCode = null,
    //     string|null $name = null,
    //     string|null $message = null,
    //     int|null $code = null,
    //     string|null $statusText = null,
    // ) {
    //     if (is_null(self::$name)) {
    //         self::$responseCode = $responseCode;
    //         self::$name = $name ?? 'already-loaded';
    //         self::$message = $message;
    //         self::$code = $code;
    //         self::$statusText = $statusText;
    //     }
    // }

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

    public static function all(): array
    {
        return (new static())->toArray();
    }

    public static function put(IResponseCode $responseCode, string|null $message = null): void
    {
        $excData = [
            'rc' => $responseCode::class,
            'name' => $responseCode->name,
            'message' => $message ?? $responseCode->message(),
        ];
        $isSecure = request()->isSecure() || (bool) config('app.secure');
        setcookie('kfn-exc', encrypt($excData), time() + 60, '/', '', $isSecure, true);
    }

    public static function get(string $key, string|int|null $default = null): string|int
    {
        $all = self::all();

        return $all[$key] ?? $default;
    }

    public static function set(): void
    {
        if (isset($_COOKIE['kfn-exc'])) {
            if (is_null(self::$name)) {
                try {
                    $excCookie = decrypt($_COOKIE['kfn-exc']) ?? [];
                    $excData = new Fluent($excCookie);
                    $rcName = $excData->name;
                    $rc = $excData->rc::tryFrom($rcName ?? '') ?? null;
                    if ($rc instanceof IResponseCode) {
                        self::$responseCode = $rc;
                        self::$name = $rcName;
                        self::$message = $excData->message ?? $rc->message();
                        self::$code = $rc->statusCode();
                        self::$statusText = $rc->statusText();
                    }
                } catch (\Throwable $throw) {
                    //
                }
            }

            self::forget();
        }
    }

    public static function forget(): void
    {
        setcookie('kfn-exc', '', 1, '/');
        // $isSecure = request()->isSecure() || (bool) config('app.secure');
        // setcookie('kfn-exc', '', 1, '/', '', $isSecure, true);
    }

    // public function __invoke()
    // {
    //     static::of(session('kfn-exception') ?? []);
    // }
}
