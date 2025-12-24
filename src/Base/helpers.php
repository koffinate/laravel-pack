<?php

use Illuminate\Http\Resources\Json\JsonResource;

if (! function_exists('fromResource')) {
    /**
     * Generate a collection from resource.
     *
     * @param JsonResource $resource
     *
     * @return mixed
     */
    function fromResource(JsonResource $resource): mixed
    {
        return json_decode(json_encode($resource));
    }
}
