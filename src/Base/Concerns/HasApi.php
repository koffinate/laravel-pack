<?php

namespace Kfn\Base\Concerns;

use Illuminate\Contracts\Pagination;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Kfn\Base\Enums\ResponseCode;
use Kfn\Base\Response;

trait HasApi
{
    /** @var string|null */
    protected string|null $accept = null;

    /** @var array */
    protected array $responseMessages;

    /**
     * Use to get response message.
     *
     * @param  string  $context
     *
     * @return string
     */
    public function getResponseMessage(string $context): string
    {
        return $this->responseMessages[$context];
    }

    /**
     * @param  array|Arrayable|CursorPaginator|JsonResource|Paginator|ResourceCollection|string|null  $data
     * @param  string|null  $message
     * @param  ResponseCode  $rc
     * @param  array  $headers
     * @param  array  $extra
     *
     * @return Response
     */
    public function response(
        array|Arrayable|JsonResource|Pagination\CursorPaginator|Pagination\Paginator|ResourceCollection|string|null $data = null,
        string|null $message = null,
        ResponseCode $rc = ResponseCode::SUCCESS,
        array $headers = [],
        array $extra = []
    ): Response {
        if (is_null($this->accept)) {
            $this->acceptJson();
        }

        return new Response($data, $message, $rc, $headers, $extra);
    }

    public function contentType(string $contentType): static
    {
        request()->headers->set('Content-Type', $contentType);

        return $this;
    }

    public function accept(string $accept): static
    {
        $this->accept = $accept;
        request()->headers->set('Accept', $accept);

        return $this;
    }

    public function acceptAny(): static
    {
        return $this->accept('*/*');
    }

    public function acceptJson(): static
    {
        return $this->accept('application/json');
    }

    public function acceptHtml(): static
    {
        return $this->accept('text/html');
    }

    public function asForm(): static
    {
        return $this->contentType('application/x-www-form-urlencoded');
    }

    public function asMultipart(): static
    {
        return $this->contentType('multipart/form-data');
    }
}
