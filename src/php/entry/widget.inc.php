<?php
require_once __DIR__ . '/../lib/lib.php';

$context = require __DIR__ . '/../lib/user-context-loader.inc.php';

$accountId = $context['accountId'];
$uid = $context['uid'];
$fio = $context['fio'];

// В демо отсутствует авторизация между виджетом и бэкендом (передаем accountId напрямую параметром) - в реальных решениях НИ В КОЕМ СЛУЧАЕ НЕ ДЕЛАЙТЕ ТАК (должна быть авторизация)!!!
$getObjectUrl = "/utils/get-object.php?accountId=$accountId&entity=$entity&objectId=";

require __DIR__ . '/widget.html.php';
