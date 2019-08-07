<?php

require_once 'lib.php';

$contextKey = $_GET['contextKey'];

loginfo("IFRAME", "Loaded iframe with contextKey: $contextKey");

$employee = vendorApi()->context($contextKey);

$uid = $employee->uid;
$fio = $employee->shortFio;
$accountId = $employee->accountId;

//$_SESSION['accountId'] = $accountId;

$isAdmin = stripos($uid, 'admin@') !== false; // todo !!! use permissions instead of uid analyze

$app = AppInstance::loadApp($accountId);
$infoMessage = $app->infoMessage;

$isSettingsRequired = $app->status != AppInstance::ACTIVATED;

$accessToken = 'TODO !!!';


require 'iframe.html.php';