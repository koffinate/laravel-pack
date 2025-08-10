<?php

namespace Kfn\Base;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Fluent;
use Kfn\Base\Contracts\ResponseCodeInterface;
use Kfn\Base\Contracts\ResponseResultInterface;
use Kfn\Base\Enums\ResponseCode;
use Kfn\Base\Enums\ResponseResult;

class Response implements Responsable
{
    private static ResponseResult $resultAs = ResponseResult::DEFAULT;

    /**
     * Response constructor.
     *
     * @param JsonResource|ResourceCollection|Arrayable|LengthAwarePaginator|CursorPaginator|array|string|null $data
     * @param string|null $message
     * @param ResponseCodeInterface $code
     * @param array $extra
     */
    public function __construct(
        protected JsonResource|ResourceCollection|Arrayable|LengthAwarePaginator|CursorPaginator|array|string|null $data = null,
        protected string|null $message = null,
        protected ResponseCodeInterface $code = ResponseCode::SUCCESS,
        protected array $extra = [],
    ) {
        //
    }

    /**
     * @inheritDoc
     *
     * @throws \JsonException
     */
    public function toResponse($request): HttpResponse|JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        if ($request->expectsJson()) {
            return response()->json($this->getResponseData(), $this->code->httpCode());
        }

        return new HttpResponse(
            json_encode($this->getResponseData(), JSON_THROW_ON_ERROR),
            $this->code->httpCode()
        );
    }

    /**
     * Get response data.
     *
     * @return array<string, mixed>
     */
    public function getResponseData(): array
    {
        $payload = match (true) {
            $this->data instanceof Paginator,
                $this->data instanceof Arrayable => $this->data->toArray(),
            ($this->data?->resource ?? null) instanceof AbstractPaginator => array_merge(
                $this->data->resource->toArray(),
                $this->getData()
            ),
            default => $this->data,
        };

        return match (static::$resultAs) {
            ResponseResult::SIMPLE => $this->getResponseSimple($payload),
            ResponseResult::SELECT2 => $this->getResponseSelect2($payload),
            default => $this->getResponseNormal($payload),
        };

        // /**
        //  * this part is not supported for laravel resource and resource collection
        //  */
        // $resp = [
        //     'rc' => $this->code->name,
        //     'message' => $this->getMessage(),
        //     'timestamp' => now(),
        // ];
        //
        // if ($this->data instanceof Paginator || $this->data instanceof CursorPaginator) {
        //     $paginatorPayload = $this->data->toArray();
        //
        //     return array_merge(
        //         $resp,
        //         Arr::except($paginatorPayload, ['data']),
        //         ['payload' => $paginatorPayload['data']],
        //     );
        // }
        //
        // if ($this->data instanceof Arrayable) {
        //     return array_merge($resp, ['payload' => $this->data->toArray()]);
        // }
        //
        // return array_merge($resp, ['payload' => $this->data]);
    }

    private function getResponseNormal(JsonResource|ResourceCollection|array|null $payload): array
    {
        $resp = array_merge([
            config('koffinate.base.result.rc_wrapper') => $this->code->name,
            'message' => $this->getMessage(),
            'timestamp' => now(),
        ], $this->extra);

        $payloadWrapper = config('koffinate.base.result.payload_wrapper');
        if ($payloadWrapper) {
            $payload = [$payloadWrapper => $payload];
        }

        return array_merge($resp, [
            self::getResultAs()->dataWrapper() => $payload,
        ]);
    }

    private function getResponseSimple(array|null $payload): array
    {
        return [
            self::getResultAs()->dataWrapper() => $payload,
        ];
    }

    private function getResponseSelect2(array|null $payload): array
    {
        return [
            self::getResultAs()->dataWrapper() => collect($payload)->map(function ($it, $i) {
                if (is_string($it)) {
                    return [
                        'id' => $i,
                        'text' => $it,
                    ];
                }

                $tmp = new Fluent($it);
                $id = null;
                $text = null;
                if(! $tmp->offsetExists('id')) {
                    $id = $tmp->get('key', $i);
                }
                if(! $tmp->offsetExists('text')) {
                    $text = $tmp->offsetExists('name')
                        ? $tmp->get('name')
                        : $tmp->get('label', '');
                }

                if (is_array($it)) {
                    if($id) $it['id'] = $id;
                    if($text) $it['text'] = $text;
                } elseif (is_object($it)) {
                    if($id) $it->id = $id;
                    if($text) $it->text = $text;
                }

                return $it;
            }),
        ];
    }

    /**
     * Get response message.
     *
     * @return string|null
     */
    public function getMessage(): string|null
    {
        return $this->message ?? $this->code->message();
    }

    /**
     * @return JsonResource|ResourceCollection|Arrayable|LengthAwarePaginator|CursorPaginator|array|null
     */
    public function getData(): JsonResource|ResourceCollection|Arrayable|LengthAwarePaginator|CursorPaginator|array|null
    {
        return $this->data instanceof Arrayable ? $this->data->toArray() : $this->data;
    }

    /**
     * @return ResponseResultInterface
     */
    public static function getResultAs(): ResponseResultInterface
    {
        return static::$resultAs;
    }

    /**
     * @param ResponseResultInterface $result
     *
     * @return void
     */
    public static function setResultAs(ResponseResultInterface $result): void
    {
        static::$resultAs = $result;
    }
}
