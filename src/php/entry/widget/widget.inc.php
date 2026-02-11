<?php

require_once __DIR__ . '/../../shared/lib/lib.php';
require_once __DIR__ . '/../../shared/lib/user-context-loader.inc.php';

// В демо отсутствует авторизация между виджетом и бэкендом (передаем accountId напрямую параметром) - в реальных решениях НИ В КОЕМ СЛУЧАЕ НЕ ДЕЛАЙТЕ ТАК (должна быть авторизация)!!!
$getObjectUrl = cfg()->appBaseUrl . "/entry/widget/get-object.php?accountId=$accountId&entity=$entity&objectId=";
