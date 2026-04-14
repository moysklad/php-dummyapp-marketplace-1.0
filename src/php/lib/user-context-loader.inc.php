<?php

// DEMO: получаем контекст пользователя по contextKey и кэшируем его в PHP-сессии.
$contextKey = trim((string)($_GET['contextKey'] ?? ''));
$context = null;

if ($contextKey !== '') {
    $context = loadUserContextFromSession($contextKey);

    if ($context) {
        log_message('DEBUG', "Loaded user context from session by contextKey: $contextKey");
    } else {
        log_message('DEBUG', "Loaded user context from Vendor API by contextKey: $contextKey");

        $employee = vendorApi()->context($contextKey);

        if (!$employee || empty($employee->accountId) || empty($employee->uid)) {
            http_response_code(401);
            exit('Ошибка авторизации: не удалось получить контекст пользователя');
        }

        $context = [
            'uid' => $employee->uid,
            'fio' => $employee->shortFio ?? '',
            'accountId' => $employee->accountId,
            'isAdmin' => normalizeIsAdmin($employee->permissions->admin->view ?? false),
        ];

        saveUserContextToSession($contextKey, $context);
    }
} else {
    http_response_code(401);
    exit('Ошибка авторизации: параметр contextKey обязателен');
}

$context['contextKey'] = $context['contextKey'] ?? $contextKey;

return $context;
