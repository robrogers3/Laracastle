<?php
return [
    'castle' => [
        'api_base_url' => 'https://api.castle.io',
        'devices_path' => 'https://api.castle.io/v1/devices/',
        'secret' => env('CASTLE_SECRET'),
        'app_id' => env('CASTLE_APP_ID'),
        'mode' => env('CASTLE_MODE', 'evaluation')
    ]
];
