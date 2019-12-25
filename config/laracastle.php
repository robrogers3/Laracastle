<?php
return [
    'castle' => [
        'secret' => env('CASTLE_SECRET'),
        'app_id' => env('CASTLE_APP_ID'),
        'mode' => env('CASTLE_MODE', 'evaluation')
    ]
];
