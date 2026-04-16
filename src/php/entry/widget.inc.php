<?php
if (!defined('WIDGET_ENTRY')) {
    http_response_code(403);
    exit('Forbidden');
}

$uid = $context['uid'];
$fio = $context['fio'];
$contextKey = $context['contextKey'] ?? '';

// Явно передаем contextKey, чтобы backend выбрал нужный контекст из сессии
// и не смешивал его с другими окнами/виджетами этого домена.
$getObjectUrl = '/utils/get-object.php?' . http_build_query([
        'entity' => $entity,
        'contextKey' => $contextKey,
    ]) . '&objectId=';

require __DIR__ . '/widget.html.php';
