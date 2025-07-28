<?php

use \Firebase\JWT\JWT;

require_once 'lib.php';
require_once 'button.php';
require_once 'jwt.lib.php';


$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'];
$headers = apache_request_headers();
log_message('DEBUG', "Received: method=$method, path=$path, headers=" . print_r($headers, true));

if (!authTokenIsValid($headers)) {
    http_response_code(401);
    exit(0);
}

$path = str_ireplace('/api/moysklad/vendor/1.0/apps/', '', $path);
$pp = explode('/', $path);
$n = count($pp);
$appId = $pp[0];
$accountId = $pp[1];

log_message('DEBUG', "Extracted: appId=$appId, accountId=$accountId");

$app = AppInstance::load($appId, $accountId);

switch ($method) {
    case 'PUT':
        $requestBody = file_get_contents('php://input');
        log_message('DEBUG', "Request body: " . print_r($requestBody, true));

        $data = json_decode($requestBody);
        $appUid = $data->appUid;
        $accessToken = $data->access[0]->access_token;

        if (!$app->getStatusName()) {
            $app->accessToken = $accessToken;
            $app->status = AppInstance::SETTINGS_REQUIRED;
            $app->persist();
        }
        replyStatus($appId, $accountId, $app->getStatusName());
        break;
    case 'POST':
        // Обработка нажатий на кастомные кнопки
        if ($pp[2] == 'button') {
            $requestBody = file_get_contents('php://input');
            log_message('DEBUG', "Request body: " . print_r($requestBody, true));

            $data = json_decode($requestBody);

            header("Content-Type: application/json");
            if (!empty($data->objectId)) {
                echo json_encode(processDocumentButtonClick($data->buttonName, $data->extensionPoint, $data->objectId, $data->user));
            } elseif (!empty($data->selected)) {
                echo json_encode(processListButtonClick($data->buttonName, $data->extensionPoint, $data->selected, $data->user));
            }
            log_message('INFO', "Button processed for appId=$appId on accountId=$accountId by user=" . print_r($data->user, true));
        }
        break;
    case 'GET':
        checkAppStatus($appId, $accountId, $app->getStatusName());
        replyStatus($appId, $accountId, $app->getStatusName());
        break;
    case 'DELETE':
        checkAppStatus($appId, $accountId, $app->getStatusName());
        $app->delete();
        log_message('INFO', "App appId=$appId deleted on accountId=$accountId");
        break;
}

function checkAppStatus($appId, $accountId, $status)
{
    if (!$status) {
        log_message('INFO', "App appId=$appId not installed on accountId=$accountId");
        http_response_code(404);
        exit(0);
    }
}

function replyStatus($appId, $accountId, $status)
{
    log_message('INFO', "App appId=$appId installed on accountId=$accountId. Status: " . $status);
    header("Content-Type: application/json");
    echo '{"status": "' . $status . '"}';
}

function authTokenIsValid($headers): bool
{
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    if (empty($auth)) {
        log_message('WARN', "Authorization header not set");
        return false;
    }

    $bearer = "Bearer ";
    if (substr($auth, 0, 7) != $bearer) {
        log_message('WARN', "Invalid auth token: $auth");
        return false;
    }
    $jwtToken = str_replace($bearer, "", $auth);

    $secretKey = cfg()->secretKey;
    if (empty($secretKey)) {
        log_message('ERROR', "Secret key is not set in config");
        return false;
    }

    try {
        $decoded = JWT::decode($jwtToken, $secretKey, ["HS256"]);
        if (empty($decoded->jti)) {
            log_message('WARN', "JTI is not set");
            return false;
        }
        // jti - является уникальным идентификатором токена.
        // Следовательно, нужно добавить проверку что ранее не было запроса с таким значением jti в токене
        // @link - https://dev.moysklad.ru/doc/api/vendor/1.0/#autentifikaciq-wzaimodejstwiq-po-vendor-api
        return true;
    } catch (Exception $exception) {
        log_message('WARN', $exception->getMessage());
        return false;
    }
}
