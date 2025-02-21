<?php

use Illuminate\Contracts\Pagination;
use Illuminate\Contracts\Support\{Arrayable,Htmlable};
use Illuminate\Support\Facades\{Session,View};
use Illuminate\Support\ViewErrorBag;

if (!function_exists('disguiseText')) {
    /**
     * @param string|int|float $plain
     * @return string
     */
    function disguiseText(string|int|float $plain): string
    {
        $disguiseText = $plain;
        if (! is_string($plain)) {
            $disguiseText = (string) $plain;
        }

        if (config('koffinate.view.obscure.enable', false)) {
            $disguise = config('koffinate.view.obscure.text', '*****');
            if (strlen($disguiseText) <= strlen($disguise)) {
                $disguiseText = $disguise;
            } else {
                $disguiseText = preg_replace(
                /** @lang text */
                    '/^(\+?\w{3})(\N+)(\w{2})$/',
                    '$1' . $disguise . '$3',
                    $disguiseText
                );
            }
        }
        return $disguiseText;
    }
}

if (!function_exists('setDefaultRequest')) {
    /**
     * Set Default Value for Request Input.
     *
     * @param string|array $name
     * @param null $value
     * @param bool $force
     *
     * @return void
     * @throws Throwable
     */
    function setDefaultRequest(string|array $name, mixed $value = null, bool $force = true): void
    {
        $request = app('request');
        if (empty(session()->get('_flash.old', []))) {
            try {
                $data = is_array($name) ? $name : array_merge(session()->getOldInput(), [$name => $value]);

                session()->flashInput($data);
                $force ? $request->merge($data) : $request->mergeIfMissing($data);
            } catch (Exception $e) {
                throw_if(app()->hasDebugModeEnabled(), $e);
            }
        }
    }
}

if (! function_exists('hasSections')) {
    function hasSections(string ...$sections): bool
    {
        $view = app('view');
        return (bool) collect($sections)->first(function ($section) use ($view) {
            return ! empty(trim($view->yieldContent($section)));
        });
    }
}

if (!function_exists('vendor')) {
    /**
     * Generate an asset path for the application.
     *
     * @param string $path
     *
     * @return string
     */
    function vendor(string $path): string
    {
        $vendorPath = config('koffinate.core.url.vendor');
        $vendorPath = $vendorPath !== '' ? $vendorPath : cachedAsset('vendor');

        if (preg_match('/(:\/\/)+/i', $path, $matches, PREG_UNMATCHED_AS_NULL, 1)) {
            $replacedCount = 0;
            $pattern = '/^(vendor:\/\/)/i';
            $path = preg_replace($pattern, '', $path, -1, $replacedCount);
            if ($replacedCount > 0) {
                $vendorPath .= '/assets';
            }

            $replacedCount = 0;
            $pattern = '/^(asset:\/\/)/i';
            $path = preg_replace($pattern, '', $path, -1, $replacedCount);
            if ($replacedCount > 0) {
                $vendorPath = cachedAsset('');
            }
        }

        if (isDev() && preg_match('/(app)((\.min)?\.css)$/i', $path)) {
            $path = preg_replace('/(app)((\.min)?\.css)$/i', '$1-dev$2', $path);
        }

        return $vendorPath . '/' . $path;
    }
}

if (!function_exists('document')) {
    /**
     * Generate an asset path for the application.
     *
     * @param string $path
     *
     * @return string
     */
    function document(string $path): string
    {
        return config('koffinate.core.url.document', asset('files')) . "/{$path}";
    }
}

if (!function_exists('plugins')) {
    /**
     * Retrieve Application Plugins.
     * retrieving from config's definitions.
     *
     * @param  string|array|null  $name
     * @param  string  $base
     * @param  string|array  $type
     *
     * @return void
     * @throws Throwable
     */
    function plugins(string|array|null $name = null, string $base = 'local', string|array $type = ['css', 'js']): void
    {
        if (!$name) {
            return;
        }
        if (!in_array($base, ['vendor', 'local'])) {
            return;
        }

        $assetType = config('koffinate.plugins.asset_type');
        $name = (array)$name;
        $type = (array)$type;

        $rs = [];
        if ($assetType == 'vite') {
            foreach ($name as $pkgKey => $pkgVal) {
                $rs = array_merge_recursive(
                    $rs,
                    viteAssets(names: $pkgVal, type: $type)
                );
            }
        } else {
            foreach ($name as $pkgKey => $pkgVal) {
                if (is_array($pkgVal)) {
                    $rs = array_merge_recursive(
                        $rs,
                        pluginAssets(names: $pkgKey, base: $base, type: $type)
                    );

                    foreach ($pkgVal as $pKey => $pVal) {
                        $rs = array_merge_recursive(
                            $rs,
                            pluginAssets(names: $pVal, base: $base, type: $type, parent: $pkgKey.'.'.$pKey.'.')
                        );
                    }
                } else {
                    $rs = array_merge_recursive(
                        $rs,
                        pluginAssets(names: $pkgVal, base: $base, type: $type)
                    );
                }
            }
        }
        $rs = fluent($rs);

        if ($assetType == 'vite') {
            $css = $rs->get('css');
            $css = !empty($css)
                ? app(Illuminate\Foundation\Vite::class)($css)->toHtml()
                : '';
            $httpCss = $rs->get('http_css');
            if (!empty($httpCss)) {
                $css .= implode('', (array) $httpCss);
            }

            $js = $rs->get('js');
            $js = !empty($js)
                ? app(Illuminate\Foundation\Vite::class)($js)->toHtml()
                : '';
            $httpJs = $rs->get('http_js');
            if (!empty($httpJs)) {
                $js .= implode('', (array) $httpJs);
            }

        } else {
            $css = $rs->get('css', '');
            if (is_array($css)) {
                $css = implode('', $css);
            }
            $js = $rs->get('js', '');
            if (is_array($js)) {
                $js = implode('', $js);
            }
        }

        View::share(['pluginCss' => $css ?? '', 'pluginJs' => $js ?? '']);
    }
}

if (!function_exists('viteAssets')) {
    /**
     * Retrieve Application Plugin's Assets.
     * retrieving from config's definitions.
     *
     * @param  array|string  $names
     * @param  array  $type
     *
     * @return array
     */
    function viteAssets(
        array|string $names,
        array $type = ['css', 'js'],
    ): array {
        $package = "koffinate.plugins.items.";
        $httpPattern = '/^(http[s?]:)/i';
        $rs = ['css' => [], 'js' => [], 'http_css' => '', 'http_js' => ''];
        foreach ((array) $names as $name) {
            foreach ($type as $t) {
                if (config()->has("{$package}{$name}.{$t}")) {
                    foreach ((array) config("{$package}{$name}.{$t}") as $file) {
                        if (preg_match($httpPattern, $file)) {
                            $rs['http_'.$t] .= match ($t) {
                                'css' => "<link href='{$file}' rel='stylesheet'>",
                                'js' => "<script type='module' src='{$file}'></script>",
                            };
                        } else {
                            $rs[$t][] = $file;
                        }
                    }
                }
            }
        }

        return $rs;
    }
}

if (!function_exists('pluginAssets')) {
    /**
     * Retrieve Application Plugin's Assets.
     * retrieving from config's definitions.
     *
     * @param array|string $names
     * @param string $base
     * @param array $type
     * @param string $parent
     *
     * @return array
     */
    function pluginAssets(
        array|string $names,
        string $base = 'local',
        array  $type = ['css', 'js'],
        string $parent = '',
    ): array {
        if (! is_array($names)) {
            $names = (array) $names;
        }

        $localPath = preg_replace('/\/+$/', '', config('koffinate.plugins.base_path', 'plugins')) . '/';
        $package = "koffinate.plugins.items.{$parent}";
        $httpPattern = '/^(http[s?]:)/i';
        $jsType = config('koffinate.plugins.script_type');
        if (!empty($jsType)) {
            $jsType = "type='{$jsType}' ";
        }

        $rs = [];
        foreach ($names as $name) {
            foreach ($type as $t) {
                $rs[$t] = '';
                if (config()->has("{$package}{$name}.{$t}")) {
                    $legacyCondition = null;
                    if ($t === 'legacy') {
                        $legacyCondition = config("{$package}{$name}.legacy")['condition'];
                        $rs[$t] .= $legacyCondition[0];
                    }

                    foreach (config("{$package}{$name}.{$t}") as $file) {
                        if (preg_match($httpPattern, $file)) {
                            $src = $file;
                        } else {
                            $src = match ($base) {
                                'vendor' => vendor($file),
                                'local' => cachedAsset($localPath.$file),
                                default => null,
                            };
                        }

                        if ($src) {
                            if ($t === 'css') {
                                $rs[$t] .= "<link href='{$src}' rel='stylesheet'>";
                            }
                            if ($t === 'js') {
                                $rs[$t] .= "<script {$jsType}src='{$src}'></script>";
                            }
                        }

                        unset($src);
                    }

                    if ($legacyCondition) {
                        $rs[$t] .= $legacyCondition[1];
                    }
                }
            }
        }

        return $rs;
    }
}

if (!function_exists('isDev')) {
    /**
     * Development Mode Checker.
     *
     * @return bool
     */
    function isDev(): bool
    {
        if (Session::has('dev_mode')) {
            return Session::get('dev_mode', false);
        }

        $dev = (string)env('APP_DEV_MODE', 'off');

        return in_array(strtolower($dev), ['true', '1', 'on']);
    }
}

if (!function_exists('activeCss')) {
    /**
     * @param string $route
     * @param array $params
     * @param string $cssClass
     *
     * @return string
     */
    function activeCss(string $route = '', array $params = [], string $cssClass = 'active current'): string
    {
        return activeRoute($route, $params) ? $cssClass : '';
    }
}

if (!function_exists('getErrors')) {
    /**
     * Get validation errors.
     *
     * @param string|null $bag
     *
     * @return ViewErrorBag|null
     */
    function getErrors(?string $bag = null): ?ViewErrorBag
    {
        $errors = session('errors');
        if (!$errors instanceof ViewErrorBag) {
            return null;
        }
        if ($bag) {
            if (empty($errors->{$bag}->all())) {
                return null;
            }
            $errors = $errors->$bag;
        }

        return $errors;
    }
}

if (!function_exists('hasError')) {
    /**
     * Exist validation error.
     *
     * @param string|array|null $key
     * @param string|null $bag
     *
     * @return bool
     */
    function hasError(string|array|null $key = null, ?string $bag = null): bool
    {
        if (($errors = getErrors($bag)) instanceof ViewErrorBag === false) {
            return false;
        }

        return $errors->has($key);
    }
}

if (!function_exists('inputFeedbackComponent')) {
    /**
     * Input feedback component
     *
     * @param string|array $message
     * @param string $mode valid|invalid
     * @param string $type feedback|tooltip
     * @param string $glue
     * @param string|null $id
     *
     * @return Htmlable
     */
    function inputFeedbackComponent(
        string|array $message,
        string $mode = 'invalid',
        string $type = 'feedback',
        string $glue = '<br>',
        string|null $id = null
    ): Htmlable {
        if (!in_array($mode, ['valid', 'invalid'])) {
            $mode = 'invalid';
        }
        if (!in_array($type, ['feedback', 'tooltip'])) {
            $type = 'feedback';
        }
        if (is_array($message)) {
            $message = implode($glue, $message);
        }

        return str("<div class='{$mode}-{$type}' id='{$id}'>{$message}</div>")->toHtmlString();
    }
}

if (!function_exists('feedbackClass')) {
    /**
     * Feedback CSS Class
     *
     * @param string|array|null $key
     * @param string|null $bag
     * @param bool $isGroup
     * @param string|null $class
     *
     * @return string
     */
    function feedbackClass(
        string|array|null $key = null,
        string|null $bag = null,
        bool $isGroup = false,
        string|null $class = null
    ): string {
        if (hasError($key, $bag)) {
            return $class ?? ($isGroup ? 'has-error' : 'is-invalid');
        }
        return '';
    }
}

if (!function_exists('feedbackInput')) {
    /**
     * InValid input feedback
     *
     * @param string|array|null $key
     * @param ?string $bag
     * @param string $type feedback|tooltip
     * @param bool $asString
     * @return Htmlable|string
     */
    function feedbackInput(
        string|array|null $key = null,
        string|null $bag = null,
        string $type = 'feedback',
        bool $asString = false
    ): Htmlable|string {
        if (empty($errors = getErrors($bag))) return '';

        if (is_array($key)) {
            $messages = [];
            foreach ($key as $k) {
                if ($errors->has($k)) {
                    $messages[] = $errors->first($k);
                }
            }
        } else {
            $messages = $errors->first($key);
        }

        return empty($messages) ? '' : inputFeedbackComponent($messages, 'invalid', $type);
    }
}

if (!function_exists('errorAll')) {
    /**
     * InValid input feedback
     *
     * @param string|null $bag
     * @param array|null $exclude
     * @return string
     */
    function errorAll(string|null $bag = null, array|null $exclude = null): string
    {
        $errors = session('errors');
        if (empty($errors)) return '';
        if ($bag) {
            if (empty($errors->$bag->all())) return '';
            // $errors = $errors->$bag;
        }
        // if (!$errors->has($key)) return '';

        return '<div class="alert alert-danger rounded-0" style="border-width: 2px; border-left: none; border-right: none;">' .
            '<h4 class="alert-heading">Eror!! <small>Periksa Lagi Inputan Anda</small></h4>' .
            '</div>';
    }
}

if (!function_exists('viewPath')) {
    /**
     * Compile view path.
     *
     * @param string|null $path
     *
     * @return string
     */
    function viewPath(string|null $path = null): string
    {
        $path = $path ?? '';

        try {
            $view = view()->shared('viewPath', '');

            return $view . $path;
        } catch (Exception $e) {
            return $path;
        }
    }
}

if (!function_exists('paginated')) {
    /**
     * Check data is paginated.
     *
     * @param  Arrayable  $source
     *
     * @return Pagination\Paginator|Pagination\CursorPaginator
     * @throws Exception
     */
    function paginated(Arrayable $source): Pagination\Paginator|Pagination\CursorPaginator
    {
        if (
            $source instanceof Pagination\Paginator ||
            $source instanceof Pagination\CursorPaginator
        ) {
            return $source;
        }

        // return null;
        throw new Exception('invalid data type');
    }
}

if (!function_exists('isPaginated')) {
    /**
     * Check data is paginated.
     *
     * @param Arrayable $source
     *
     * @return bool
     */
    function isPaginated(Arrayable $source): bool
    {
        try {
            return (
                $source instanceof Pagination\Paginator ||
                $source instanceof Pagination\CursorPaginator
            );
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('paginatedStyleReset')) {
    /**
     * Style reset paginate.
     *
     * @param Arrayable $source
     *
     * @return string
     */
    function paginatedStyleReset(Arrayable $source): string
    {
        try {
            $source = paginated($source);
            $numReset = $source->perPage() * ($source->currentPage() - 1);

            return "counter-reset: _rownum {$numReset};";
        } catch (Exception $e) {
            return '';
        }
    }
}

if (!function_exists('paginatedLink')) {
    /**
     * Generate paginate links.
     *
     * @param Arrayable $source
     * @param string|null $view
     * @param array $data
     *
     * @return Htmlable
     */
    function paginatedLink(
        Arrayable $source,
        string|null       $view = null,
        array             $data = [],
    ): Htmlable
    {
        try {
            $source = paginated($source);
            return $source->links($view, $data);
        } catch (Exception $e) {
            return str()->toHtmlString();
        }
    }
}

if (!function_exists('cachedAsset')) {
    /**
     * @param string $path
     * @param bool $secure
     *
     * @return string
     */
    function cachedAsset(string $path, bool|null $secure = null): string
    {
        $asset = str($path)->is('/^https?:\/\//i')
            ? $path
            : asset($path, $secure);

        return $asset . '?_v=' . config('cache.version', time());
    }
}

if (! function_exists('includeIf')) {
    /**
     * @param  string|null  $path
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    function includeIf(string|null $path = null): void
    {
        if (!$path) return;

        $view = app('view');
        if ($view->exists($path)) {
            echo $view->make(
                $path,
                \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path'])
            )->render();
        }
    }
}

if (! function_exists('methodIf')) {
    /**
     * @param  bool  $condition
     * @param  string  $method
     *
     * @return \Illuminate\Support\HtmlString
     */
    function methodIf(bool $condition, string $method): \Illuminate\Support\HtmlString
    {
        return $condition
            ? method_field($method)
            : new \Illuminate\Support\HtmlString('');
    }
}

if (!function_exists('hasStack')) {
    /**
     * @param  string|null  $name
     *
     * @return bool
     */
    function hasStack(string|null $name = null): bool
    {
        return $name && !empty(view()->yieldPushContent($name));
    }
}
