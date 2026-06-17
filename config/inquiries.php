<?php

return [
    'recipient' => [
        'address' => env('INQUIRY_MAIL_TO_ADDRESS') ?: env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('INQUIRY_MAIL_TO_NAME') ?: env('MAIL_FROM_NAME', env('APP_NAME', 'CenterThis')),
    ],

    'subject' => env('INQUIRY_MAIL_SUBJECT', 'New website inquiry'),
];
