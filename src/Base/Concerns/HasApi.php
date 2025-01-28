<?php

namespace Kfn\Base\Concerns;

use Illuminate\Contracts\Pagination;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Kfn\Base\Enums\ResponseCode;
use Kfn\Base\Response;

trait HasApi
{
    protected array $responseMessages;

    /**
     * Use to get response message
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
     * @param  Arrayable<int|string, mixed>|Pagination\Paginator<Eloquent\Model>|Pagination\CursorPaginator<Eloquent\Model>|array<int|string, mixed>|string|null  $data
     * @param  string|null  $message
     * @param  ResponseCode  $rc
     *
     * @return Response
     */
    public function response(
        JsonResource|ResourceCollection|Arrayable|Pagination\Paginator|Pagination\CursorPaginator|array|string|null $data = null,
        ?string                                                                                                     $message = null,
        ResponseCode                                                                                                $rc = ResponseCode::SUCCESS,
    ): Response {
        return new Response($data, $message, $rc);
    }

}
