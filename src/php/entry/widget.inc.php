<?php
if (!defined('WIDGET_ENTRY')) {
    http_response_code(403);
    exit('Forbidden');
}

if (!isset($context) || !is_array($context)) {
    throw new LogicException('widget.inc.php requires a user context array');
}

if (!isset($entity) || !is_string($entity) || $entity === '') {
    throw new LogicException('widget.inc.php requires a non-empty entity name');
}

/** @var array{uid: string, fio: string, contextKey?: string} $context */
$uid = (string)$context['uid'];
$fio = (string)$context['fio'];
$contextKey = (string)($context['contextKey'] ?? '');

// Явно передаем contextKey, чтобы backend выбрал нужный контекст из сессии
// и не смешивал его с другими окнами/виджетами этого домена.
$getObjectUrl = '/utils/get-object.php?' . http_build_query([
        'entity' => $entity,
        'contextKey' => $contextKey,
    ]) . '&objectId=';

require __DIR__ . '/widget.html.php';
