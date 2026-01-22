<?php

use Illuminate\Http\Resources\Json\JsonResource;

return [
    /**
     * Force json response.
     */
    'force_json' => true,
    'force_json_prefixes' => ['api'],
    'api_prefixes' => ['api'],

    /**
     * Cache handling.
     */
    'cache' => [
        'handling' => (bool) env('KFN_CACHE_HANDLING', false),

        /**
         * Skip keys from invalidated handling.
         * fill the key with: null, array, or string.
         */
        'skip_invalidate_keys' => env('KFN_CACHE_SKIP_KEYS', null),
    ],

    /**
     * Debugging handler.
     */
    'debug' => [
        /**
         * How to trace exception will be rendered.
         * available values: debug, string, array.
         */
        'trace_mode' => (string) env('KFN_TRACE_MODE', 'debug'),
    ],

    /**
     * Configure the result wrapper.
     */
    'result' => [
        'rc_wrapper' => 'rc',
        'data_wrapper' => JsonResource::$wrap,
        'payload_wrapper' => null,
    ],
];
