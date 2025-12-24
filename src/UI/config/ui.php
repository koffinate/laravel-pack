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

    /**
     * The url to be used.
     */
    'url' => [
        'document' => env('KFN_DOCUMENT_URL', ''),
        'vendor' => env('KFN_VENDOR_URL', ''),
    ],

    /**
     * The feedback template to be used.
     *
     * fill with null to default template.
     * or your custom html tag
     *
     * with available variables:
     *  - :feedback-class:  => to render feedback class
     *  - :id:  => to render tag id
     *  - :message:  => to render feedback message
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
         * handling method if exception occurs.
         * available values: redirect, abort
         * default method is "abort".
         */
        'handling_method' => 'abort',

        /**
         * redirect to url.
         * available values: back, url-string.
         */
        'redirect_to' => 'back',

        /**
         * fallback url
         * will be used when exception occurs and fail on redirected back to the previous url.
         */
        'fallback_url' => '/',
    ],

];
