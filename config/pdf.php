<?php

return [
    'mode'                  => 'utf-8',
    'format'                => 'A4',
    'author'                => '',
    'subject'               => '',
    'keywords'              => '',
    'creator'               => 'Laravel Pdf',
    'display_mode'          => 'fullpage',
    'tempDir'               => storage_path('app/mpdf'),
    'pdf_a'                 => false,
    'pdf_a_auto'            => false,
    'icc_profile_path'      => '',
    'font_path' => base_path('public/fonts/'),
    'font_data' => [
        'kalpurush' => [
            'R'  => 'kalpurush.ttf',    // regular
            'B'  => 'kalpurush.ttf',    // optional: bold
            'I'  => 'kalpurush.ttf',    // optional: italic
            'BI' => 'kalpurush.ttf',    // optional: bold-italic
            'useOTL' => 0xFF,
            'useKashida' => 75,
        ]
    ]
];
