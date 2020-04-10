<?php

return [
    'connection' => [
        'timeout' => 10,
        'iana' => 'whois.iana.org',
        'max-loops' => 512
    ],
    'proxy' => [
        'host' => '127.0.0.1',
        'port' => 8080
    ],
    'patterns' => [
        'whois' => [
            'registrar whois server:',
            'whois:',
            'refer:'
        ]
    ],
];
