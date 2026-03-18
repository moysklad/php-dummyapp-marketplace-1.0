<?php
/*
 * Логирование ошибок для отладки. Отключите перед публикацией решения!
 */
ini_set('log_errors', '1');
ini_set('display_errors', '1');
error_reporting(E_ALL);

const LOG_LEVEL = 'DEBUG';

/**
 * Укажите ваши значения конфигурационных параметров в этом файле, либо заполните соотв. переменные окружения
 */
return [
    'appId' => '195d5446-9da8-47ee-abb9-e808e4f283d7',
    'appUid' => 'php-demo-app.moysklad',
    'appBaseUrl' => 'http://php-demo-app',
    'secretKey' => 'nAbioPF2HAuYvrYpOikD3LYnNTzkYGugXqRT74hUGD47BeLEY7Zo7rHM4EK0wcj4oSAycrDpbVYhO44XdmKYtTEKzepbO4g6LzfYfU7c1ILRTfcGJOPpJTMkV8mwltJx'
];
