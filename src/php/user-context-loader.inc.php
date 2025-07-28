<?php

$contextKey = $_GET['contextKey'];
log_message('DEBUG', "Loaded iframe with contextKey: $contextKey");
$employee = vendorApi()->context($contextKey);

$uid = $employee->uid;
$fio = $employee->shortFio;
$accountId = $employee->accountId;

$isAdmin = $employee->permissions->admin->view;

