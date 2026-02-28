<?php

namespace Kfn\UI;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\MessageProvider;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Fluent;
use Kfn\Base\Contracts\IResponseCode;
use Kfn\Base\Enums\ResponseCode;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response extends \Kfn\Base\Response implements Responsable
{
    private Response|string|View|null $view = null;
    private Fluent $viewOption;
    private string|null $redirect = null;
    private Fluent $redirectOption;
    private array $with = [];

    /** {@inheritdoc} */
    public function __construct(
        array|Arrayable|CursorPaginator|JsonResource|Paginator|ResourceCollection|string|null $data = null,
        string|null $message = null,
        IResponseCode $code = ResponseCode::SUCCESS,
        array $headers = [],
        array $extra = []
    ) {
        parent::__construct($data, $message, $code, $headers, $extra);
        $this->redirectOption = new Fluent;
        $this->viewOption = new Fluent;
    }

    /** {@inheritdoc} */
    public function toResponse($request): HttpResponse|JsonResponse|SymfonyResponse
    {
        if (! $request->expectsJson()) {
            if (in_array($this->redirect, ['back', 'to', 'intended', 'action', 'route'])) {
                $response = null;
                $this->handleRedirect($request, $response);

                if ($response instanceof RedirectResponse) {
                    return $response->send();
                }
            }
            if (! is_null($this->view)) {
                return new HttpResponse($this->handleView());
            }
        }

        return parent::toResponse($request);
    }

    /**
     * @param  Request  $request
     * @param  mixed|null  $redirect
     *
     * @return void
     */
    private function handleRedirect(Request $request, mixed &$redirect = null): void
    {
        if ($this->redirect === 'route' && ! app('router')->has($this->redirectOption->get('target'))) {
            $this->redirect = 'back';
        }

        $redirect = match ($this->redirect) {
            'to' => app('redirect')->to(
                $this->redirectOption->get('target'),
                (int) $this->redirectOption->get('status', 302),
                (array) $this->redirectOption->get('headers', []),
                $this->redirectOption->get('secure'),
            ),
            'intended' => app('redirect')->intended(
                $this->redirectOption->get('target', '/'),
                (int) $this->redirectOption->get('status', 302),
                (array) $this->redirectOption->get('headers', []),
                $this->redirectOption->get('secure'),
            ),
            'action' => app('redirect')->action(
                $this->redirectOption->get('target'),
                (array) $this->redirectOption->get('params', []),
                (int) $this->redirectOption->get('status', 302),
                (array) $this->redirectOption->get('headers', []),
            ),
            'route' => app('redirect')->route(
                $this->redirectOption->get('target'),
                (array) $this->redirectOption->get('params', []),
                (int) $this->redirectOption->get('status', 302),
                (array) $this->redirectOption->get('headers', []),
            ),
            default => app('redirect')->back(
                (int) $this->redirectOption->get('status', 302),
                (array) $this->redirectOption->get('headers', []),
                $this->redirectOption->get('fallback', false),
            ),
        };

        foreach ($this->with as $wKey => $wValue) {
            $redirect = match ($wKey) {
                'w_input' => $redirect->withInput($wValue),
                'w_cookie' => $redirect->withCookie($wValue),
                'w_cookies' => $redirect->withCookies($wValue),
                'w_errors' => $redirect->withErrors($wValue['property'], $wValue['key']),
                default => $redirect->with($wKey, $wValue),
            };
        }

        if ($request->ajax()) {
            $this->extra['redirect'] = $redirect->getTargetUrl();
            $redirect = null;
        }
    }

    private function handleView(): string
    {
        if ($this->view instanceof View) {
            return $this->view->render();
        }
        if ($this->view instanceof static) {
            $this->viewOption = $this->view->viewOption;
            $this->view = $this->view->view;
        }

        return view($this->view, $this->viewOption->get('data') ?: [])->render();
    }

    public function view(Response|string|View $view, array $data = [], int $status = 200, array $headers = []): static
    {
        $this->view = $view;
        $this->viewOption['data'] = $data;
        $this->viewOption['status'] = $status;
        $this->viewOption['headers'] = $headers;

        return $this;
    }

    public function to(string|null $path = null, int $status = 302, array $headers = [], bool|null $secure = null): static
    {
        $this->redirect = 'to';
        $this->redirectOption['target'] = $path;
        $this->redirectOption['status'] = $status;
        $this->redirectOption['headers'] = $headers;
        $this->redirectOption['secure'] = $secure;

        return $this;
    }

    public function intended(string $default = '/', int $status = 302, array $headers = [], bool|null $secure = null): static
    {
        $this->redirect = 'intended';
        $this->redirectOption['target'] = $default;
        $this->redirectOption['status'] = $status;
        $this->redirectOption['headers'] = $headers;
        $this->redirectOption['secure'] = $secure;

        return $this;
    }

    public function route(\BackedEnum|string $route, array $parameters = [], int $status = 302, array $headers = []): static
    {
        $this->redirect = 'route';
        $this->redirectOption['target'] = $route;
        $this->redirectOption['params'] = $parameters;
        $this->redirectOption['status'] = $status;
        $this->redirectOption['headers'] = $headers;

        return $this;
    }

    public function action(array|string $action, array $parameters = [], int $status = 302, array $headers = []): static
    {
        $this->redirect = 'action';
        $this->redirectOption['target'] = $action;
        $this->redirectOption['params'] = $parameters;
        $this->redirectOption['status'] = $status;
        $this->redirectOption['headers'] = $headers;

        return $this;
    }

    public function back(int $status = 302, array $headers = [], mixed $fallback = false): static
    {
        // app('redirect')->back();
        $this->redirect = 'back';
        $this->redirectOption['status'] = $status;
        $this->redirectOption['headers'] = $headers;
        $this->redirectOption['fallback'] = $fallback;

        return $this;
    }

    public function with(array|string $key, mixed $value = null): static
    {
        $this->with[$key] = $value;

        return $this;
    }

    public function withErrors(array|MessageProvider|string $provider, $key = 'default'): static
    {
        $this->with['w_errors'] = [
            'provider' => $provider,
            'key' => $key,
        ];

        return $this;
    }

    public function withInput(array|null $input = null): static
    {
        $this->with['w_input'] = $input;

        return $this;
    }

    public function withCookies(array $cookies): static
    {
        $this->with['w_cookies'] = $cookies;

        return $this;
    }

    public function withCookie($cookie): static
    {
        $this->with['w_cookie'] = $cookie;

        return $this;
    }
}
