<?php

require_once 'lib.php';

$infoMessage = $_POST['infoMessage'];
$store = $_POST['store'];

loginfo('UPDATE-SETTINGS', "Update info message: $infoMessage, store: $store");

$accountId = $_POST['accountId'];

$app = AppInstance::loadApp($accountId);
$app->infoMessage = $infoMessage;
$app->store = $store;

$notify = $app->status != AppInstance::ACTIVATED;
$app->status = AppInstance::ACTIVATED;

// так как PUT - идемпотентный метод, можем дергать несколько раз или можем только один раз при первой активации дергать
//if ($notify) {
    vendorApi()->updateAppStatus(cfg()->appId, $accountId, $app->getStatusName());
//}

$app->persist();

echo 'Настройки обновлены, перезагрузите приложение';
