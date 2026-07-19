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
        'enable' => (bool) env('KFN_UI_OBSCURE', false),
        'text' => env('KFN_UI_OBSCURE_TEXT', '*****'),
    ],

    /**
     * The url to be used.
     */
    'url' => [
        'document' => env('KFN_UI_DOCUMENT_URL', ''),
        'vendor' => env('KFN_UI_VENDOR_URL', ''),
    ],

    'html' => [
        'meta' => [
            ['name' => 'description', 'content' => ''],
            ['name' => 'keywords', 'content' => 'yusronarif, koffinate, laravel, php'],
            ['name' => 'author', 'content' => 'Yusron Arif <yusron.arif4::at::gmail.com>'],
            ['name' => 'generator', 'content' => 'Koffinate'],
        ],
    ],

    /**
     * The feedback template to be used.
     *
     * fill with null to default template.
     * or your custom HTML tag
     *
     * with available variables:
     *   - :feedback-class: => to render feedback class
     *   - :id: => to render tag id
     *   - :message: => to render a feedback message
     *
     * e.g. <div class=":feedback-class:" id=":id:">:message:</div>
     */
    'feedback' => [
        'template' => null,
    ],

    /**
     * exception handling.
     */
    'exception' => [
        /**
         * enabling exception handling.
         */
        'enabled' => env('KFN_UI_HANDLING', true),

        /**
         * handling method if exception occurs.
         * available values: redirect, abort
         * default method is "abort".
         */
        'handling_method' => env('KFN_UI_HANDLING_METHOD', 'abort'),

        /**
         * redirect to url.
         * available values: back, url-string.
         */
        'redirect_to' => env('KFN_UI_HANDLING_REDIRECT_TO', 'back'),

        /**
         * fallback url
         * will be used when exception occurs and fail on redirected back to the previous url.
         */
        'fallback_url' => env('KFN_UI_HANDLING_FALLBACK_URL', '/'),
    ],

];
