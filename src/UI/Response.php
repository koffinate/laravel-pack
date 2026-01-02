<?php

namespace Kfn\UI;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\MessageProvider;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
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
    private string|View|Response|null $view = null;
    private Fluent $viewOption;
    private string|null $redirect = null;
    private Fluent $redirectOption;
    private array $with = [];

    /** @inheritdoc */
    public function __construct(
        array|string|JsonResource|ResourceCollection|Arrayable|Paginator|CursorPaginator|null $data = null,
        string|null $message = null,
        IResponseCode $code = ResponseCode::SUCCESS,
        array $headers = [],
        array $extra = []
    ) {
        parent::__construct($data, $message, $code, $headers, $extra);
        $this->redirectOption = new Fluent();
        $this->viewOption = new Fluent();
    }

    /** @inheritdoc */
    public function toResponse($request): HttpResponse|JsonResponse|SymfonyResponse
    {
        if (! $request->expectsJson()) {
            if (in_array($this->redirect, ['back', 'to', 'intended', 'action', 'route'])) {
                $this->handleRedirect($request);
            }
            if (! is_null($this->view)) {
                return new HttpResponse($this->handleView());
            }
        }

        return parent::toResponse($request);
    }

    private function handleRedirect(Request $request): void
    {
        if ('route' === $this->redirect && ! app('router')->has($this->redirectOption->get('target'))) {
            $this->redirect = 'back';
        }

        $redirect = match ($this->redirect) {
            'to' => redirect()->to(
                $this->redirectOption->get('target'),
                (int) $this->redirectOption->get('status', 302),
                (array) $this->redirectOption->get('headers', []),
                $this->redirectOption->get('secure'),
            ),
            'intended' => redirect()->intended(
                $this->redirectOption->get('target', '/'),
                (int) $this->redirectOption->get('status', 302),
                (array) $this->redirectOption->get('headers', []),
                $this->redirectOption->get('secure'),
            ),
            'action' => redirect()->action(
                $this->redirectOption->get('target'),
                (array) $this->redirectOption->get('params', []),
                (int) $this->redirectOption->get('status', 302),
                (array) $this->redirectOption->get('headers', []),
            ),
            'route' => redirect()->route(
                $this->redirectOption->get('target'),
                (array) $this->redirectOption->get('params', []),
                (int) $this->redirectOption->get('status', 302),
                (array) $this->redirectOption->get('headers', []),
            ),
            default => redirect()->back(
                (int) $this->redirectOption->get('status', 302),
                (array) $this->redirectOption->get('headers', []),
                $this->redirectOption->get('fallback', false),
            ),
        };

        foreach ($this->with as $wKey => $wValue) {
            switch ($wKey) {
                case 'w_input':
                    $redirect->withInput($wValue);
                    break;
                case 'w_cookie':
                    $redirect->withCookie($wValue);
                    break;
                case 'w_cookies':
                    $redirect->withCookies($wValue);
                    break;
                case 'w_errors':
                    $redirect->withErrors($wValue['property'], $wValue['key']);
                    break;
                default:
                    $redirect->with($wKey, $wValue);
            }
        }

        if ($request->ajax()) {
            $this->extra['redirect'] = $redirect->getTargetUrl();

            return;
        }

        $redirect->send();
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

    public function view(string|View|Response $view, array $data = [], int $status = 200, array $headers = []): static
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

    public function route(string|\BackedEnum $route, array $parameters = [], int $status = 302, array $headers = []): static
    {
        $this->redirect = 'route';
        $this->redirectOption['target'] = $route;
        $this->redirectOption['params'] = $parameters;
        $this->redirectOption['status'] = $status;
        $this->redirectOption['headers'] = $headers;

        return $this;
    }

    public function action(string|array $action, array $parameters = [], int $status = 302, array $headers = []): static
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
        redirect()->back();
        $this->redirect = 'back';
        $this->redirectOption['status'] = $status;
        $this->redirectOption['headers'] = $headers;
        $this->redirectOption['fallback'] = $fallback;

        return $this;
    }

    public function with(string|array $key, mixed $value = null): static
    {
        $this->with[$key] = $value;

        return $this;
    }

    public function withErrors(MessageProvider|array|string $provider, $key = 'default'): static
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
