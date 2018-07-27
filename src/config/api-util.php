<?php
/**
 * Created by PhpStorm.
 * User: caojiayuan
 * Date: 17-9-8
 * Time: 上午11:35
 */

return [
    'separate_log_file' => env('SEPARATE_LOG_FILE', true),
    'signature' => [
        'key' => env('API_SIGNATURE_KEY', 'somerandomstring'),
        'nonce_length' => env('API_NONCE_LENGTH', 16),
    ]
];
