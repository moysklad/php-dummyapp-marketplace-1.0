<?php
/*
 * Логирование ошибок для отладки. Отключите перед публикацией решения!
 */
ini_set('log_errors', '1');
ini_set('display_errors', '1');
error_reporting(E_ALL);

const LOG_LEVEL = 'DEBUG';
// const LOG_LEVEL = 'INFO';

/**
 * Укажите ваши значения конфигурационных параметров в этом файле, либо заполните соотв. переменные окружения
 */
return [
    'appId' => getenv('APP_ID'),             // '195d5446-9da8-47ee-abb9-e808e4f283d7'
    'appUid' => getenv('APP_UID'),           // 'php-demo-app.moysklad
    'appBaseUrl' => getenv('APP_BASE_URL'),  // 'https://php-demo.testms-test.lognex.ru'
    'secretKey' => getenv('APP_SECRET_KEY'), // 'nAbioPF2HAuYvrYpOikD3LYnNTzkYGugXqRT74hUGD47BeLEY7Zo7rHM4EK0wcj4oSAycrDpbVYhO44XdmKYtTEKzepbO4g6LzfYfU7c1ILRTfcGJOPpJTMkV8mwltJx'
    'databasePath' => getenv('APP_DB_PATH'), // '/var/www/html/src/php/data/app.sqlite'
    'encryptKey' => getenv('APP_ENCRYPT_KEY'), // generate: bin2hex(sodium_crypto_secretbox_keygen())
];
