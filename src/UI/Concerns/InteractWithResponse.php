<?php

namespace Kfn\UI\Concerns;

use Kfn\UI\Response;

trait InteractWithResponse
{
    /**
     * @param  string|null  $path
     * @param  int  $status
     * @param  array  $headers
     * @param  bool|null  $secure
     *
     * @return Response
     */
    public function to(string|null $path = null, int $status = 302, array $headers = [], bool|null $secure = null): Response
    {
        $this->validateResponse();
        return $this->response()->to($path, $status, $headers, $secure);
    }

    /**
     * @param  string  $default
     * @param  int  $status
     * @param  array  $headers
     * @param  bool|null  $secure
     *
     * @return Response
     */
    public function intended(string $default = '/', int $status = 302, array $headers = [], bool|null $secure = null): Response
    {
        $this->validateResponse();
        return $this->response()->intended($default, $status, $headers, $secure);
    }

    /**
     * @param  string|\BackedEnum  $route
     * @param  array  $parameters
     * @param  int  $status
     * @param  array  $headers
     *
     * @return Response
     */
    public function route(string|\BackedEnum $route, array $parameters = [], int $status = 302, array $headers = []): Response
    {
        $this->validateResponse();
        return $this->response()->route($route, $parameters, $status, $headers);
    }

    /**
     * @param  string|array  $action
     * @param  array  $parameters
     * @param  int  $status
     * @param  array  $headers
     *
     * @return Response
     */
    public function action(string|array $action, array $parameters = [], int $status = 302, array $headers = []): Response
    {
        $this->validateResponse();
        return $this->response()->action($action, $parameters, $status, $headers);
    }

    /**
     * @param  int  $status
     * @param  array  $headers
     * @param  mixed  $fallback
     *
     * @return Response
     */
    public function back(int $status = 302, array $headers = [], mixed $fallback = false): Response
    {
        $this->validateResponse();
        return $this->response()->back($status, $headers, $fallback);
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function validateResponse(): void
    {
        if (!method_exists($this, 'response')) {
            throw new \Exception('Method `response` does not exist.');
        }
    }
}
