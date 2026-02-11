<?php

require_once __DIR__ . '/../../shared/lib/lib.php';

$contextName = 'IFRAME';

require_once __DIR__ . '/../../shared/lib/user-context-loader.inc.php';

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
