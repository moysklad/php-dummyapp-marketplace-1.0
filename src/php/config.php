<?php
/*
 * Логирование ошибок для отладки. Отключите перед публикацией решения!
 */
ini_set('log_errors', '1');
ini_set('display_errors', '1');
error_reporting(E_ALL);

const LOG_LEVEL = 'DEBUG';
//const LOG_LEVEL = 'INFO';

/**
 * Укажите ваши значения конфигурационных параметров в этом файле, либо заполните соотв. переменные окружения
 */
return [                                            // Примеры значений:
    'appId' => getenv('APP_ID'),            // 'ac02196f-e0ea-4818-b142-991455ea62bc'
    'appUid' => getenv('APP_UID'),          // 'dummyapp.lognex'
    'appBaseUrl' => getenv('APP_BASE_URL'), // 'https://dummyapp.example.com'
    'secretKey' => getenv('APP_SECRET_KEY'),// 'nAbioPF2HAuYvrYpOikD3LYnNTzkYGugXqRT74hUGD47BeLEY7Zo7rHM4EK0wcj4oSAycrDpbVYhO44XdmKYtTEKzepbO4g6LzfYfU7c1ILRTfcGJOPpJTMkV8mwltJx'
];
