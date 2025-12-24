<?php

use Illuminate\Http\Resources\Json\JsonResource;

return [
    'force_json' => true,
    'force_json_prefixes' => ['api'],
    'api_prefixes' => ['api'],

    /**
     * Debugging handler
     */
    'debug' => [
        /**
         * How to trace exception will be rendered.
         * available values: debug, string, array
         */
        'trace_mode' => (string) env('KFN_TRACE_MODE', 'debug'),
    ],

    /**
     * Configure the result wrapper
     */
    'result' => [
        'rc_wrapper' => 'rc',
        'data_wrapper' => JsonResource::$wrap,
        'payload_wrapper' => null,
    ],
];
