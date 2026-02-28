<?php

use Illuminate\Http\Resources\Json\JsonResource;

if (! function_exists('cacheIsHandling')) {
    /**
     * Is cache handling enabled?
     *
     * @return bool
     */
    function cacheIsHandling(): bool
    {
        $handling = config('koffinate.base.cache.handling', false);

        return is_bool($handling) && $handling;
    }
}

if (! function_exists('fromResource')) {
    /**
     * Generate a collection from resource.
     *
     * @param  JsonResource  $resource
     *
     * @return mixed
     */
    function fromResource(JsonResource $resource): mixed
    {
        return json_decode(json_encode($resource));
    }
}
