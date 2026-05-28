<?php

return [
    'installed' => (bool) env('SSPR_INSTALLED', false),

    'ldap' => [
        'host' => env('LDAP_HOST'),
        'port' => (int) env('LDAP_PORT', 389),
        'base_dn' => env('LDAP_BASE_DN'),
        'username' => env('LDAP_USERNAME'),
        'password' => env('LDAP_PASSWORD'),
        'reset_username' => env('LDAP_RESET_USERNAME', env('LDAP_USERNAME')),
        'reset_password' => env('LDAP_RESET_PASSWORD', env('LDAP_PASSWORD')),
        'use_ssl' => (bool) env('LDAP_SSL', false),
        'use_tls' => (bool) env('LDAP_TLS', false),
        'timeout' => (int) env('LDAP_TIMEOUT', 5),
    ],

    'otp' => [
        'expires_minutes' => (int) env('SSPR_OTP_EXPIRES_MINUTES', 10),
        'max_attempts' => (int) env('SSPR_OTP_MAX_ATTEMPTS', 5),
    ],

    'sms' => [
        'endpoint' => env('SMS_ENDPOINT'),
        'token' => env('SMS_TOKEN'),
        'from' => env('SMS_FROM'),
        'test_to' => env('SMS_TEST_TO'),
    ],
];
