<?php
if (!defined('IFRAME_ENTRY')) {
    http_response_code(403);
    exit('Forbidden');
}

$contextName = 'IFRAME';

$accountId = $context['accountId'];
$isAdmin = $context['isAdmin'];
$uid = $context['uid'];
$fio = $context['fio'];
$contextKey = $context['contextKey'] ?? '';

$app = AppInstance::loadApp($accountId);

$infoMessage = $app->infoMessage;
$store = $app->store;
$isSettingsRequired = $app->status != AppInstance::ACTIVATED;
$storesValues = [];

if ($isAdmin) {
    $stores = jsonApi()->stores();

    foreach ($stores->rows as $v) {
        $storesValues[] = $v->name;
    }
}

require __DIR__ . '/iframe.html.php';
