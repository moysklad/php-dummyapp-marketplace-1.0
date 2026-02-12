<?php
require_once __DIR__ . '/../lib/lib.php';

$context = require __DIR__ . '/../lib/user-context-loader.inc.php';

$contextName = 'IFRAME';

$accountId = $context['accountId'];
$isAdmin = $context['isAdmin'];
$uid = $context['uid'];
$fio = $context['fio'];

$app = AppInstance::loadApp($accountId);

$infoMessage = $app->infoMessage;
$store = $app->store;
$isSettingsRequired = $app->status != AppInstance::ACTIVATED;

if ($isAdmin) {
    $stores = jsonApi()->stores();
    $storesValues = [];

    foreach ($stores->rows as $v) {
        $storesValues[] = $v->name;
    }
}

require __DIR__ . '/iframe.html.php';
