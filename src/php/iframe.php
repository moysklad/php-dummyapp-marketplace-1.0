<?php

require_once 'lib.php';

$contextKey = $_GET['contextKey'];

loginfo("IFRAME", "Loaded iframe with contextKey: $contextKey");

$employee = vendorApi()->context($contextKey);

$uid = $employee->uid;
$fio = $employee->shortFio;
$accountId = $employee->accountId;

$isAdmin = $employee->permissions->admin->view;

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

require 'iframe.html.php';