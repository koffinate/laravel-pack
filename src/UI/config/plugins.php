<?php

/**
 * UI plugins dependency list.
 *
 * @author      yusron arif <yusron.arif4@gmail.com>
 */
return [
    /*
     * Base path on public folder
     */
    'base_path' => 'assets/plugins',

    /*
     * Asset type
     * e.g. null, vite, etc.
     * or leave blank
     */
    'asset_type' => null,

    /*
     * Script tag type attribute
     * fill with native script type attribute
     * e.g. text/javascript, module, etc.
     * or leave blank
     */
    'script_type' => 'text/javascript',

    /*
     * Mapping of plugin items
     */
    'items' => [
        // WYSIWYG Editor
        'summernote' => [
            // ref: https://summernote.org/getting-started/#basic-api
            'css' => ['https://cdn.jsdelivr.net/npm/summernote@latest/dist/summernote.min.css'],
            'js' => ['https://cdn.jsdelivr.net/npm/summernote@latest/dist/summernote.min.js'],
        ],
        'ckeditor' => [
            // ref: https://ckeditor.com/ckeditor-5/download/#cdn
            'css' => ['https://cdn.ckeditor.com/ckeditor5/43.3.1/ckeditor5.css'],
            'js' => ['https://cdn.ckeditor.com/ckeditor5/43.3.1/ckeditor5.js'],
        ],
        'tinymce' => [
            // ref: https://www.tiny.cloud/
            'css' => ['https://cdn.jsdelivr.net/npm/tinymce@7.5.1/skins/ui/oxide/content.min.css'],
            'js' => ['https://cdn.jsdelivr.net/npm/tinymce@7.5.1/tinymce.min.js'],
        ],
        'froala' => [
            // ref: https://froala.com/wysiwyg-editor/docs/overview/
            'css' => ['https://cdn.jsdelivr.net/npm/froala-editor@latest/css/froala_editor.pkgd.min.css'],
            'js' => ['https://cdn.jsdelivr.net/npm/froala-editor@latest/js/froala_editor.pkgd.min.js'],
        ],
        'quill' => [
            // ref: https://quilljs.com/
            'css' => ['https://cdn.jsdelivr.net/npm/quill@latest/dist/quill.snow.min.css'],
            'js' => ['https://cdn.jsdelivr.net/npm/quill'],
        ],

        // Charts
        'apexchart' => [
            // ref: https://apexcharts.com/docs/installation/
            'css' => ['https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.css'],
            'js' => ['https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js'],
        ],
        'flotcharts' => [
            // ref: http://www.flotcharts.org/
            'css' => [],
            'js' => ['https://cdn.jsdelivr.net/npm/flot-charts@latest/jquery.flot.min.js'],
        ],

        // Tables
        'datatables' => [
            // ref: https://datatables.net
            'css' => [
                'https://cdn.jsdelivr.net/npm/datatables.net-bs5@latest/css/dataTables.bootstrap5.min.css',
            ],
            'js' => [
                'https://cdn.jsdelivr.net/npm/datatables.net@latest/js/dataTables.min.js',
                'https://cdn.jsdelivr.net/npm/datatables.net-bs5@latest/js/dataTables.bootstrap5.min.js',
            ],
        ],
        'tabulator' => [
            // ref: https://tabulator.info/docs/
            'css' => ['https://unpkg.com/tabulator-tables/dist/css/tabulator.min.css'],
            'js' => ['https://unpkg.com/tabulator-tables/dist/js/tabulator.min.js'],
        ],

        // Calendar & Date Picker
        'fullcalendar' => [
            // ref: https://fullcalendar.io/docs/initialize-globals
            'css' => [],
            'js' => ['https://cdn.jsdelivr.net/npm/fullcalendar'],
        ],
        'daterangepicker' => [
            // ref: http://www.daterangepicker.com/
            'css' => ['https://cdn.jsdelivr.net/npm/daterangepicker@latest/daterangepicker.min.css'],
            'js' => ['https://cdn.jsdelivr.net/npm/daterangepicker@latest/daterangepicker.min.js'],
        ],
        'flatpickr' => [
            // ref: https://flatpickr.js.org
            'css' => ['https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css'],
            'js' => ['https://cdn.jsdelivr.net/npm/flatpickr'],
        ],

        // Custom Form
        'select2' => [
            // ref: https://select2.org/
            'css' => [
                'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
                'https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css',
            ],
            'js' => [
                'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
            ],
        ],
        'dropzone' => [
            // ref: https://docs.dropzone.dev
            'css' => ['https://unpkg.com/dropzone@5/dist/min/dropzone.min.css'],
            'js' => ['https://unpkg.com/dropzone@5/dist/min/dropzone.min.js'],
        ],
        'inputmask' => [
            // ref: https://robinherbots.github.io/Inputmask/
            'css' => ['https://cdn.jsdelivr.net/npm/inputmask@5.0.9/dist/colormask.min.css'],
            'js' => ['https://cdn.jsdelivr.net/npm/inputmask'],
        ],
        'nouislider' => [
            // ref: https://refreshless.com/nouislider/
            'css' => ['https://cdn.jsdelivr.net/npm/nouislider@15.8.1/dist/nouislider.min.css'],
            'js' => ['https://cdn.jsdelivr.net/npm/nouislider'],
        ],
        'tiny-slider' => [
            // ref: http://ganlanyuan.github.io/tiny-slider
            'css' => ['https://cdn.jsdelivr.net/npm/tiny-slider@2.9.4/dist/tiny-slider.min.css'],
            'js' => ['https://cdn.jsdelivr.net/npm/tiny-slider'],
        ],
        'tagify' => [
            // ref: https://yaireo.github.io/tagify
            'css' => ['https://cdn.jsdelivr.net/npm/@yaireo/tagify@4.32.1/dist/tagify.min.css'],
            'js' => ['https://cdn.jsdelivr.net/npm/@yaireo/tagify'],
        ],

        // Maps
        'leaflet' => [
            // ref: http://leafletjs.com/
            'css' => ['https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css'],
            'js' => ['https://cdn.jsdelivr.net/npm/leaflet'],
        ],

        // UI
        'countup' => [
            // ref: https://inorganik.github.io/countUp.js
            'css' => [],
            'js' => ['https://cdn.jsdelivr.net/npm/countup.js'],
        ],
        'fslightbox' => [
            // ref: https://fslightbox.com/
            'css' => [],
            'js' => ['https://cdn.jsdelivr.net/npm/fslightbox'],
        ],
        'jkanban' => [
            // ref: http://www.riccardotartaglia.it/jkanban/
            'css' => ['https://cdn.jsdelivr.net/npm/jkanban@1.3.1/dist/jkanban.min.css'],
            'js' => ['https://cdn.jsdelivr.net/npm/jkanban'],
        ],
        'jstree' => [
            // ref: https://www.jstree.com/
            'css' => ['https://cdn.jsdelivr.net/npm/jstree@3.3.17/dist/themes/default/style.min.css'],
            'js' => ['https://cdn.jsdelivr.net/npm/jstree'],
        ],
        'prismjs' => [
            // ref: https://prismjs.com/
            'css' => ['https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism.min.css'],
            'js' => ['https://cdn.jsdelivr.net/npm/prismjs'],
        ],
        'typedjs' => [
            // ref: http://mattboldt.github.io/typed.js/docs
            'css' => [],
            'js' => ['https://cdn.jsdelivr.net/npm/typed.js'],
        ],
        'vis-timeline' => [
            // ref: https://visjs.github.io/vis-timeline/
            'css' => ['https://cdn.jsdelivr.net/npm/vis-timeline@7.7.3/styles/vis-timeline-graph2d.min.css'],
            'js' => ['https://cdn.jsdelivr.net/npm/vis-timeline'],
        ],
    ],
];
