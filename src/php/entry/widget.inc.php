<?php
require_once __DIR__ . '/../lib/lib.php';

$context = require __DIR__ . '/../lib/user-context-loader.inc.php';

$accountId = $context['accountId'];

// В демо отсутствует авторизация между виджетом и бэкендом (передаем accountId напрямую параметром) - в реальных решениях НИ В КОЕМ СЛУЧАЕ НЕ ДЕЛАЙТЕ ТАК (должна быть авторизация)!!!
$getObjectUrl = cfg()->appBaseUrl . "/utils/get-object.php?accountId=$accountId&entity=$entity&objectId=";

require __DIR__ . '/widget.html';
