<?php

return [
    'hash' => 'sha1', // optional => md5 ...
    'entities' => [
        'users.store' => [ // route name
            'response' => 'custom message', // optional
            'timeout' => 600, // optional
            'signature' =>
                [
                    'body' => // required
                        [
                            'name',
                            'email',
                            'password'
                        ],
                    'headers' => // optional
                        [
                            'host'
                        ],
                    'more' => // optional
                        [
                            'users.store'
                        ],
                    'server' => // optional
                        [
                            'SERVER_PROTOCOL',
                            'REMOTE_ADDR'
                        ]
                ],
        ],
    ],
    'redis' =>
        [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
        ]
];
