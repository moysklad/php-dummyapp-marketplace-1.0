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
$isInstallStateMissing = empty($app->accessToken);

if ($isAdmin) {
    try {
        $stores = jsonApi()->stores();

        if (!empty($stores->rows)) {
            foreach ($stores->rows as $v) {
                $storesValues[] = $v->name;
            }
        }
    } catch (RuntimeException $e) {
        log_message('WARN', "Cannot fetch stores: " . $e->getMessage());
    }
}

if ($isInstallStateMissing) {
    log_message('WARN', "App appId={$app->appId} on accountId=$accountId has no access token in local storage");
}

require __DIR__ . '/iframe.html.php';
