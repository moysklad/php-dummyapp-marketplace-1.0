<?php

require_once 'lib.php';
require_once 'button.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'];

//Проверка токена авторизации
if (!authTokenIsValid()) {
    http_response_code(401);
    exit(0);
}

loginfo("MOYSKLAD => APP", "Received: method=$method, path=$path");

$path = str_ireplace('/api/moysklad/vendor/1.0/apps/', '', $path);
$pp = explode('/', $path);
$n = count($pp);
$appId = $pp[0];
$accountId = $pp[1];

logdebug("MOYSKLAD => APP", "Extracted: appId=$appId, accountId=$accountId");

$app = AppInstance::load($appId, $accountId);
$replyStatus = true;

switch ($method) {
    case 'PUT':
        $requestBody = file_get_contents('php://input');

        loginfo("MOYSKLAD => APP", "Request body: " . print_r($requestBody, true));

        $data = json_decode($requestBody);

        $appUid = $data->appUid;
        $accessToken = $data->access[0]->access_token;

        loginfo("MOYSKLAD => APP", "Received access_token: appUid=$appUid, access_token=$accessToken)");

        if (!$app->getStatusName()) {
            $app->accessToken = $accessToken;
            $app->status = AppInstance::SETTINGS_REQUIRED;
            $app->persist();
        }
        break;
    case 'POST':
        // Обработка нажатий на кастомные кнопки
        if ($pp[2] == 'button') {
            $requestBody = file_get_contents('php://input');
            loginfo("MOYSKLAD => APP", "Request body: " . print_r($requestBody, true));
            $data = json_decode($requestBody);

            header("Content-Type: application/json");
            if (!empty($data->objectId)) {
                echo json_encode(processDocumentButtonClick($data->buttonName, $data->extensionPoint, $data->objectId, $data->user));
            } elseif (!empty($data->selected)) {
                echo json_encode(processListButtonClick($data->buttonName, $data->extensionPoint, $data->selected, $data->user));
            }
        }
        $replyStatus = false;
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


