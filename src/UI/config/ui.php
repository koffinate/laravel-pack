<?php

return [

    'breeze' => [
        /**
         * The view to be used for serve on breeze.
         * possible values: blade, inertia, api.
         */
        'type' => 'blade',
    ],

    'obscure' => [
        'enable' => (bool) env('KFN_VIEW_OBSCURE', false),
        'text' => env('KFN_VIEW_OBSCURE_TEXT', '*****'),
    ],

];
