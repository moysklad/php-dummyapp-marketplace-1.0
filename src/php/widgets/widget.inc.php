<?php

$dirRoot = '../';

require_once '../lib.php';
require_once '../user-context-loader.inc.php';

// В демо отсутствует авторизация между виджетом и бэкендом (передаем accountId напрямую параметром) - в реальных решениях НИ В КОЕМ СЛУЧАЕ НЕ ДЕЛАЙТЕ ТАК (должна быть авторизация)!!!
$getObjectUrl = cfg()->appBaseUrl . "/widgets/get-object.php?accountId=$accountId&entity=$entity&objectId=";

require 'widget.html.php';
