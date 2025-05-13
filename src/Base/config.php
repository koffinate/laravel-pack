<?php

use Illuminate\Http\Resources\Json\JsonResource;

return [
    'force_json' => true,
    'force_json_prefixes' => ['api'],
    'api_prefixes' => ['api'],

    'result' => [
        'rc_wrapper' => 'rc',
        'data_wrapper' => JsonResource::$wrap,
        'payload_wrapper' => null,
    ],
];
