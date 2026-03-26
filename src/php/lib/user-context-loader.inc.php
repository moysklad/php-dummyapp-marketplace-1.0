<?php

// DEMO ONLY: reads user context via contextKey and caches it in PHP session for examples.
$contextKey = trim((string)($_GET['contextKey'] ?? ''));
$contextSource = null;
$context = null;

if ($contextKey !== '') {
    $context = loadUserContextFromSession($contextKey);

    if ($context) {
        log_message('DEBUG', "Loaded user context from session by contextKey: $contextKey");
        $contextSource = 'session';
    } else {
        log_message('DEBUG', "Loaded iframe with contextKey: $contextKey");

        $employee = vendorApi()->context($contextKey);

        if (!$employee || empty($employee->accountId) || empty($employee->uid)) {
            http_response_code(502);
            exit('Не удалось получить контекст пользователя по contextKey');
        }

        $context = [
            'uid' => $employee->uid,
            'fio' => $employee->shortFio ?? '',
            'accountId' => $employee->accountId,
            'isAdmin' => (bool)($employee->permissions->admin->view ?? false),
        ];

        saveUserContextToSession($contextKey, $context);
        $contextSource = 'vendor-api';
    }
} else {
    http_response_code(400);
    exit('Параметр contextKey обязателен');
}

$context['contextKey'] = $context['contextKey'] ?? $contextKey;
$context['contextSource'] = $contextSource;

return array_merge($context, buildSessionContextMeta());
