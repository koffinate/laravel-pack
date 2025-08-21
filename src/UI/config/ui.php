<?php

use Kfn\UI\Enums\RenderType;
use Kfn\UI\Enums\UIType;

return [

    /**
     * The render method to be used.
     */
    'render_type' => RenderType::RESPONSE,

    /**
     * The view to be used for serve on breeze.
     * possible values: blade, inertia, api.
     */
    'type' => UIType::BLADE,

    /**
     * The obscure text to be used.
     */
    'obscure' => [
        'enable' => (bool) env('KFN_VIEW_OBSCURE', false),
        'text' => env('KFN_VIEW_OBSCURE_TEXT', '*****'),
    ],

    'url' => [
        'document' => env('KFN_DOCUMENT_URL', ''),
        'vendor' => env('KFN_VENDOR_URL', ''),
    ],

];
