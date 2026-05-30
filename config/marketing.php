<?php

return [

    'banner' => [
        // 0 — без лимита приложения, действует только upload_max_filesize PHP
        'max_upload_kb' => (int) env('MARKETING_BANNER_MAX_UPLOAD_KB', 0),
    ],

    'promotion' => [
        'max_upload_kb' => (int) env('MARKETING_PROMOTION_MAX_UPLOAD_KB', 0),
    ],

];
