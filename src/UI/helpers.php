<?php

use Illuminate\Contracts\Pagination;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ViewErrorBag;
use Kfn\UI\ViewAssetManager;

if (! function_exists('disguiseText')) {
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
                    '$1'.$disguise.'$3',
                    $disguiseText
                );
            }
        }

        return $disguiseText;
    }
}

if (! function_exists('obscureText')) {
    /**
     * @param string|int|float $plain
     * @return string
     */
    function obscureText(string|int|float $plain): string
    {
        return disguiseText($plain);
    }
}

if (! function_exists('setDefaultRequest')) {
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

if (! function_exists('vendor')) {
    /**
     * Generate an asset path for the application.
     *
     * @param string $path
     *
     * @return string
     */
    function vendor(string $path): string
    {
        return ViewAssetManager::vendor($path);
    }
}

if (! function_exists('document')) {
    /**
     * Generate an asset path for the application.
     *
     * @param string $path
     *
     * @return string
     */
    function document(string $path): string
    {
        return ViewAssetManager::document($path);
    }
}

if (! function_exists('plugins')) {
    /**
     * Retrieve Application Plugins.
     * retrieving from config's definitions.
     *
     * @param  string|array|null  $name
     * @param  string  $source
     *
     * @return \Kfn\UI\Contracts\ViewAssetManager
     */
    function plugins(string|array|null $name = null, string $source = 'local'): \Kfn\UI\Contracts\ViewAssetManager
    {
        $plugin = ViewAssetManager::init();
        if ($name) {
            return $plugin->add($name, $source);
        }

        return $plugin;
    }
}

if (! function_exists('pluginScript')) {
    /**
     * Get Plugin Script.
     *
     * @return \Illuminate\Support\HtmlString
     */
    function pluginScript(): \Illuminate\Support\HtmlString
    {
        return ViewAssetManager::script();
    }
}

if (! function_exists('pluginStyle')) {
    /**
     * Get Plugin Style.
     *
     * @return \Illuminate\Support\HtmlString
     */
    function pluginStyle(): \Illuminate\Support\HtmlString
    {
        return ViewAssetManager::style();
    }
}

if (! function_exists('isDev')) {
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

        $dev = (string) env('APP_DEV_MODE', 'off');

        return in_array(strtolower($dev), ['true', '1', 'on']);
    }
}

if (! function_exists('activeCss')) {
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

if (! function_exists('getErrors')) {
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
        if (! $errors instanceof ViewErrorBag) {
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

if (! function_exists('hasError')) {
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

        return $errors->hasAny($key);
    }
}

if (! function_exists('inputFeedbackComponent')) {
    /**
     * Input feedback component.
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
        if (! in_array($mode, ['valid', 'invalid'])) {
            $mode = 'invalid';
        }
        if (! in_array($type, ['feedback', 'tooltip'])) {
            $type = 'feedback';
        }
        if (is_array($message)) {
            $message = implode($glue, $message);
        }

        $template = config('koffinate.ui.feedback.template')
            ?: '<div class=":feedback-class:" id=":id:">:message:</div>';

        return str($template)->replace(
            [':feedback-class:', ':id:', ':message:'],
            [$mode.'-'.$type, $id ?? uniqid('kfn-feedback-'), $message]
        )->toHtmlString();
    }
}

if (! function_exists('feedbackClass')) {
    /**
     * Feedback CSS Class.
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

if (! function_exists('feedbackInput')) {
    /**
     * InValid input feedback.
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
        if (empty($errors = getErrors($bag))) {
            return '';
        }

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

if (! function_exists('errorAll')) {
    /**
     * InValid input feedback.
     *
     * @param string|null $bag
     * @param array|null $exclude
     * @return string
     */
    function errorAll(string|null $bag = null, array|null $exclude = null): string
    {
        $errors = session('errors');
        if (empty($errors)) {
            return '';
        }
        if ($bag) {
            if (empty($errors->$bag->all())) {
                return '';
            }
            // $errors = $errors->$bag;
        }
        // if (!$errors->has($key)) return '';

        return '<div class="alert alert-danger rounded-0" style="border-width: 2px; border-left: none; border-right: none;">'.
            '<h4 class="alert-heading">Eror!! <small>Periksa Lagi Inputan Anda</small></h4>'.
            '</div>';
    }
}

if (! function_exists('viewPath')) {
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

            return $view.$path;
        } catch (Exception $e) {
            return $path;
        }
    }
}

if (! function_exists('paginated')) {
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

if (! function_exists('isPaginated')) {
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
            return
                $source instanceof Pagination\Paginator ||
                $source instanceof Pagination\CursorPaginator;
        } catch (Exception $e) {
            return false;
        }
    }
}

if (! function_exists('paginatedStyleReset')) {
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

if (! function_exists('paginatedLink')) {
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
        string|null $view = null,
        array $data = [],
    ): Htmlable {
        try {
            $source = paginated($source);

            return $source->links($view, $data);
        } catch (Exception $e) {
            return str()->toHtmlString();
        }
    }
}

if (! function_exists('cachedAsset')) {
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
        $version = config('cache.version');
        if (! $version) {
            $version = cache()->flexible('kfn-cache-version', [60, 70], fn () => uniqid());
        }

        return $asset.'?_v='.$version;
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
        if (! $path) {
            return;
        }

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

if (! function_exists('hasStack')) {
    /**
     * @param  string|null  $name
     *
     * @return bool
     */
    function hasStack(string|null $name = null): bool
    {
        return $name && ! empty(view()->yieldPushContent($name));
    }
}
