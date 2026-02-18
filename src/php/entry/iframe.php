<?php
require_once __DIR__ . '/../lib/lib.php';

$context = require __DIR__ . '/../lib/user-context-loader.inc.php';

$contextName = 'IFRAME';

$app = AppInstance::loadApp($context['accountId']);

$infoMessage = $app->infoMessage;
$store = $app->store;
$isSettingsRequired = $app->status != AppInstance::ACTIVATED;
$storesValues = [];

if ($context['isAdmin']) {
    $stores = jsonApi()->stores();

    foreach ($stores->rows as $v) {
        $storesValues[] = $v->name;
    }
}

require __DIR__ . '/iframe.inc.php';
