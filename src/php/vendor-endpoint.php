<?php

require_once 'lib.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'];

loginfo("MOYSKLAD => APP", "Received: method=$method, path=$path");

$pp = explode('/', $path);
$n = count($pp);
$appId = $pp[$n - 2];
$accountId = $pp[$n - 1];

$requestBody = file_get_contents('php://input');
$data = json_decode($requestBody);

$appUid = $data->appUid;
$accessToken = $data->access[0]->access_token;

loginfo("MOYSKLAD => APP", "(appId = $appId, accountId = $accountId, appUid = $appUid, access_token = $accessToken)");
loginfo("MOYSKLAD => APP", "Request body:\n$requestBody");

$app = AppInstance::load($appId, $accountId);
$replyStatus = true;

switch ($method) {
    case 'PUT':
        if (!$app->getStatusName()) {
            $app->accessToken = $accessToken;
            $app->status = AppInstance::SETTINGS_REQUIRED;
            $app->persist();
        }
        break;
    case 'GET':
        break;
    case 'DELETE':
        $app->delete();
        $replyStatus = false;
        break;
}

if (!$app->getStatusName()) {
    http_response_code(404);
} else if ($replyStatus) {
    header("Content-Type: application/json");
    echo '{"status": "' . $app->getStatusName() . '"}';
}


