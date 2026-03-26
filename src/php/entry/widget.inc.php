<?php
if (!defined('WIDGET_ENTRY')) {
    http_response_code(403);
    exit('Forbidden');
}

$uid = $context['uid'];
$fio = $context['fio'];
$contextKey = $context['contextKey'] ?? '';
$sessionId = $context['sessionId'] ?? '';
$contextHistory = $context['contextHistory'] ?? [];
$contextSourceNames = [
    'vendor-api' => 'Vendor API (по contextKey)',
    'session' => 'PHP сессия (кэш по contextKey)',
];
$contextSource = $contextSourceNames[$context['contextSource']] ?? ($context['contextSource'] ?? 'unknown');
$contextToken = buildBackendContextToken($context);

$getObjectUrl = "/utils/get-object.php?entity=$entity&objectId=";

require __DIR__ . '/widget.html.php';
