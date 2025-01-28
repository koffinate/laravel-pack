<?php

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Resources\Json\JsonResource;

if (!function_exists('user')) {
    /**
     * @param string|null $guard
     * @return Authenticatable|null
     */
    function user(string|null $guard = null): Authenticatable|null
    {
        return auth($guard)->check()
            ? auth($guard)->user()
            : null;
    }
}

if (!function_exists('fromResource')) {
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
