<?php

require_once 'lib.php';

$infoMessage = $_POST['infoMessage'];

loginfo('UPDATE-SETTINGS', "Update info message: $infoMessage");

$accountId = $_POST['accountId'];

$app = AppInstance::loadApp($accountId);
$app->infoMessage = $infoMessage;

$notify = $app->status != AppInstance::ACTIVATED;
$app->status = AppInstance::ACTIVATED;

$app->persist();

echo 'Настройки обновлены';

//if ($notify) {
    vendorApi()->updateAppStatus(cfg()->appId, $accountId, $app->getStatusName());
//}



