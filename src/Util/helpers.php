<?php

use Illuminate\Contracts\Database;
use Illuminate\Support\Carbon;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

if (! function_exists('f')) {
    /**
     * Formatting text as HTML support.
     *
     * @param  string  $text
     *
     * @return string
     */
    function f(string $text = ''): string
    {
        return stripslashes(nl2br($text));
    }
}

if (! function_exists('prettySize')) {
    /**
     * Human-readable file size.
     *
     * @param  int  $bytes
     * @param  int  $decimals
     *
     * @return string
     */
    function prettySize(int $bytes, int $decimals = 2): string
    {
        $sz = 'BKMGTPE';
        $factor = (int) floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)).($sz[$factor] ?? '');
    }
}

if (! function_exists('trimAll')) {
    /**
     * @param  null|string  $string
     * @param  string  $type
     * @param  string  $pattern
     *
     * @return string
     *
     * @throws Exception
     */
    function trimAll(string|null $string, string $type = 'smart', string $pattern = '\W+'): string
    {
        if (! $string || trim($string) == '') {
            return '';
        }
        if (! in_array($type, ['smart', 'both', 'left', 'right', 'all'])) {
            throw new Exception('type of trim not valid, use smart|left|right|all instead.', 401);
        }

        try {
            return match ($type) {
                'both' => preg_replace('/^'.$pattern.'|'.$pattern.'$/i', '', $string),
                'left' => preg_replace('/^'.$pattern.'/i', '', $string),
                'right' => preg_replace('/'.$pattern.'$/i', '', $string),
                'all' => preg_replace('/'.$pattern.'/i', '', $string),
                default => preg_replace(
                    '/'.$pattern.'/i',
                    ' ',
                    preg_replace('/^'.$pattern.'|'.$pattern.'$/i', '', $string),
                ),
            };
        } catch (Exception $e) {
        }

        return '';
    }
}

if (! function_exists('base64_encrypt')) {
    /**
     * @param  string|null  $plain
     * @param  bool  $twice  twice encryption
     *
     * @return string
     */
    function base64_encrypt(string|null $plain = null, bool $twice = false): string
    {
        if (! $plain) {
            return '';
        }

        $rand1 = str()->random(10);
        $rand2 = str()->random(10);
        $plain = str($plain)
            ->toBase64()
            ->replaceMatches('/^(.{5})(.*)(.{5})$/', '$3:rand1:$2:rand2:$1')
            ->replace([':rand1:', ':rand2:'], [$rand1, $rand2]);
        if ($twice) {
            $plain = $plain->toBase64();
        }

        return $plain->toString();
    }
}

if (! function_exists('base64_decrypt')) {
    /**
     * @param  string|null  $cheaper
     * @param  bool  $twice  twice encryption
     *
     * @return string
     */
    function base64_decrypt(string|null $cheaper = null, bool $twice = false): string
    {
        if (! $cheaper) {
            return '';
        }

        $cheaper = str($cheaper);
        if ($twice) {
            $cheaper = $cheaper->fromBase64();
        }

        return $cheaper
            ->replaceMatches('/^(.{5})(.{10})(.*)(.{10})(.{5})$/', '$5$3$1')
            ->fromBase64()
            ->toString();
    }
}

if (! function_exists('carbon')) {
    /**
     * @param  string|DateTimeInterface|null  $datetime
     * @param  DateTimeZone|string|null  $timezone
     * @param  string|DateTimeZone|null  $fromTimezone
     * @param  string|null  $locale
     *
     * @return Carbon
     */
    function carbon(
        string|DateTimeInterface|null $datetime = null,
        string|DateTimeZone|null $timezone = null,
        string|DateTimeZone|null $fromTimezone = null,
        string|null $locale = null,
    ): Carbon {
        if (auth()->check()) {
            if (! $timezone) {
                $timezone = auth()->user()?->timezone ?? null;
            }
            if (! $locale) {
                $locale = auth()->user()?->locale ?? null;
            }
        }

        $fromTimezone ??= config('app.timezone');
        $timezone ??= config('app.client_timezone') ?: $fromTimezone;
        $locale ??= config('app.client_locale') ?: config('app.locale') ?: config('app.fallback_locale');

        try {
            Carbon::setLocale($locale);
        } catch (Exception $e) {
            //
        }

        $carbon = $datetime
            ? Carbon::parse($datetime, $fromTimezone)
            : Carbon::now($fromTimezone);

        return empty($timezone) ? $carbon : $carbon->timezone($timezone);
    }
}

if (! function_exists('carbonFormat')) {
    /**
     * @param  string|DateTimeInterface|null  $datetime
     * @param  string  $isoFormat
     * @param  string|null  $format
     * @param  string|DateTimeZone|null  $timezone
     * @param  string|DateTimeZone|null  $fromTimezone
     * @param  bool  $showTz
     *
     * @return string
     */
    function carbonFormat(
        string|DateTimeInterface|null $datetime,
        string $isoFormat = 'L LT',
        string|null $format = null,
        string|DateTimeZone|null $timezone = null,
        string|DateTimeZone|null $fromTimezone = null,
        bool|null $showTz = null,
    ): string {
        $fromTimezone ??= config('app.timezone');
        $timezone ??= config('app.client_timezone') ?: $fromTimezone;
        $showTz ??= config('app.client_show_timezone', config('app.show_timezone')) || null;
        $timezoneLabel = '';
        if ($showTz) {
            $timezoneSuffix = match (str($timezone)->slug()->toString()) {
                '7',    // +7
                '70',   // +7:0
                '700',  // +7:00
                '0700',
                'asiajakarta' => 'WIB',
                '8',    // +8
                '80',   // +8:0
                '800',  // +8:00
                '0800',
                'asiamakassar' => 'WITA',
                '9',    // +9
                '90',   // +9:0
                '900',  // +9:00
                '0900',
                'asiajayapura' => 'WIT',
                default => $timezone,
            };
            $timezoneLabel = ' '.$timezoneSuffix;
        }

        if (is_null($datetime)) {
            return '';
        }

        if (is_string($datetime)) {
            try {
                $datetime = Carbon::parse($datetime, $fromTimezone);
            } catch (Exception $e) {
                return '';
            }
        }

        return ($format
            ? $datetime->timezone($timezone)->format($format)
            : $datetime->timezone($timezone)->isoFormat($isoFormat)
        ).$timezoneLabel;
    }
}

if (! function_exists('carbonFromFormat')) {
    /**
     * @param  string  $date
     * @param  string  $format
     * @param  string|DateTimeZone|null  $timezone
     * @param  string|DateTimeZone|null  $fromTimezone
     *
     * @return Carbon|null
     */
    function carbonFromFormat(
        string $date,
        string $format = 'Y-m-d H:i:s',
        string|DateTimeZone|null $timezone = null,
        string|DateTimeZone|null $fromTimezone = null,
    ): Carbon|null
    {
        $fromTimezone ??= config('app.timezone');
        $timezone ??= config('app.client_timezone') ?: $fromTimezone;

        try {
            return Carbon::createFromFormat($format, $date, $fromTimezone)->timezone($timezone);
        } catch (Exception $e) {
            return null;
        }
    }
}

if (! function_exists('numberFormat')) {
    /**
     * @param  float|int|null  $number
     * @param  int  $decimal
     *
     * @return string
     */
    function numberFormat(float|int|null $number = null, int $decimal = 0): string
    {
        if (! $number) {
            return '0';
        }

        return number_format($number, $decimal, ',', '.');
    }
}

if (! function_exists('toSentry')) {
    /**
     * @param  Throwable  $throw
     *
     * @return void
     */
    function toSentry(Throwable $throw): void
    {
        if (app()->bound('sentry') && ! app()->isLocal()) {
            \Sentry\Laravel\Integration::captureUnhandledException($throw);
        }
    }
}

if (! function_exists('fluent')) {
    /**
     * @param  array|object|null  $data
     *
     * @return Fluent
     * @throws Throwable
     */
    function fluent(array|object|null $data = null): Fluent
    {
        $tmpData = $data;

        return toFluent($tmpData);
    }
}

if (! function_exists('toFluent')) {
    /**
     * @param  array|object|null  $data
     *
     * @return Fluent
     * @throws Throwable
     */
    function toFluent(array|object|null &$data = null): Fluent
    {
        if (! $data instanceof Fluent) {
            try {
                $data = new Fluent($data ?? []);
            } catch (Exception $e) {
                if (app()->hasDebugModeEnabled()) {
                    throw $e;
                }
                $data = new Fluent();
            }
        }

        return $data;
    }
}

if (! function_exists('throwOnDebug')) {
    /**
     * @param  Throwable  $throw
     *
     * @return void
     * @throws Throwable
     */
    function throwOnDebug(Throwable $throw): void
    {
        if (app()->hasDebugModeEnabled()) {
            throw $throw;
        }
    }
}

if (! function_exists('hasRoute')) {
    /**
     * Existing Route by Name.
     *
     * @param  string|array  $name
     *
     * @return bool
     */
    function hasRoute(string|array $name): bool
    {
        return app('router')->has($name);
    }
}

if (! function_exists('routed')) {
    /**
     * Existing Route by Name
     * with '#' fallback.
     *
     * @param  string  $name
     * @param  string|array  $params
     * @param  bool  $absolute
     *
     * @return string
     */
    function routed(string $name, string|array $params = [], bool $absolute = true): string
    {
        if (hasRoute($name)) {
            return app('url')->route($name, $params, $absolute);
        }

        return '#';
    }
}

if (! function_exists('to_routed')) {
    /**
     * Existing Route by Name
     * with '#' fallback.
     *
     * @param  string  $name
     * @param  string|array  $params
     * @param  int  $status
     * @param  array  $headers
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    function to_routed(string $name, string|array $params = [], int $status = 302, array $headers = []): \Illuminate\Http\RedirectResponse
    {
        try {
            return redirect()->route($name, $params, $status, $headers);
        } catch (Exception $e) {
            return back($status, $headers);
        }
    }
}

if (! function_exists('activeRoute')) {
    /**
     * @param  string|array  $route
     * @param  string|array  $params
     *
     * @return bool
     */
    function activeRoute(string|array $route = [], string|array $params = []): bool
    {
        if (empty($route = trim($route))) {
            return false;
        }

        try {
            if (is_string($route)) {
                $route = explode('|', $route);
            }
            $routes = collect($route);
            $routes = $routes->each(fn ($it) => $routes->push($it.'.*'))->toArray();

            if (request()->routeIs($routes)) {
                if (empty($params)) {
                    return true;
                }

                $requestRoute = request()->route();
                $paramNames = $requestRoute->parameterNames();

                foreach ($params as $key => $value) {
                    if (is_int($key)) {
                        $key = $paramNames[$key];
                    }

                    if (
                        $requestRoute->parameter($key) instanceof \Illuminate\Database\Eloquent\Model
                        && $value instanceof \Illuminate\Database\Eloquent\Model
                        && $requestRoute->parameter($key)->id != $value->id
                    ) {
                        return false;
                    }

                    if (is_object($requestRoute->parameter($key))) {
                        // try to check param is enum type
                        try {
                            if ($requestRoute->parameter($key)->value && $requestRoute->parameter(
                                $key,
                            )->value != $value) {
                                return false;
                            }
                        } catch (Exception $e) {
                            return false;
                        }
                    } else {
                        if ($requestRoute->parameter($key) != $value) {
                            return false;
                        }
                    }
                }

                return true;
            }
        } catch (Exception $e) {
        }

        return false;
    }
}

if (! function_exists('getRawSql')) {
    /**
     * @param  Database\Eloquent\Builder|Database\Query\Builder  $query
     *
     * @return string
     */
    function getRawSql(
        Database\Eloquent\Builder|Database\Query\Builder $query,
    ): string {
        return Str::replaceArray('?', $query->getBindings(), $query->toSql());
    }
}
