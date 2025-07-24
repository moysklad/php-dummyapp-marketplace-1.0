<?php

use \Firebase\JWT\JWT;

require_once 'jwt.lib.php';

if (!isset($dirRoot)) {
    $dirRoot = '';
}

//
//  Config
//

class AppConfig
{
    var $appId = '';
    var $appUid = '';
    var $secretKey = '';
    var $appBaseUrl = '';

    var $moyskladVendorApiEndpointUrl = 'https://apps-api.moysklad.ru/api/vendor/1.0';
    var $moyskladJsonApiEndpointUrl = 'https://api.moysklad.ru/api/remap/1.2';

    public function __construct(array $cfg)
    {
        foreach ($cfg as $k => $v) {
            $this->$k = $v;
        }
    }
}

$cfg = new AppConfig(require('config.php'));

function cfg(): AppConfig
{
    return $GLOBALS['cfg'];
}

//
//  Vendor API 1.0
//

class VendorApi
{

    function context(string $contextKey)
    {
        return $this->request('POST', '/context/' . $contextKey);
    }

    function updateAppStatus(string $appId, string $accountId, string $status)
    {
        return $this->request('PUT',
            "/apps/$appId/$accountId/status",
            "{\"status\": \"$status\"}");
    }

    private function request(string $method, $path, $body = null)
    {
        return makeHttpRequest(
            $method,
            cfg()->moyskladVendorApiEndpointUrl . $path,
            buildJWT(),
            $body);
    }
}

function makeHttpRequest(string $method, string $url, string $bearerToken, $body = null)
{
    log_message('DEBUG', "Send: $method $url\n$body");

    $opts = $body
        ? array('http' =>
            array(
                'method' => $method,
                'header' => array('Authorization: Bearer ' . $bearerToken, "Accept-Encoding: gzip", "Content-type: application/json"),
                'content' => $body
            )
        )
        : array('http' =>
            array(
                'method' => $method,
                'header' => array('Authorization: Bearer ' . $bearerToken, "Accept-Encoding: gzip")
            )
        );
    $context = stream_context_create($opts);
    $result = file_get_contents($url, false, $context);
    log_message('DEBUG', "Response: $method $url\n$result");

    return json_decode($result);
}

$vendorApi = new VendorApi();

function vendorApi(): VendorApi
{
    return $GLOBALS['vendorApi'];
}

function buildJWT()
{
    $token = array(
        "sub" => cfg()->appUid,
        "iat" => time(),
        "exp" => time() + 300,
        "jti" => bin2hex(random_bytes(32))
    );
    return JWT::encode($token, cfg()->secretKey);
}

//
//  JSON API 1.2
//

class JsonApi
{

    private $accessToken;

    function __construct(string $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    function stores()
    {
        return makeHttpRequest(
            'GET',
            cfg()->moyskladJsonApiEndpointUrl . '/entity/store',
            $this->accessToken);
    }

    function getObject($entity, $objectId)
    {
        return makeHttpRequest(
            'GET',
            cfg()->moyskladJsonApiEndpointUrl . "/entity/$entity/$objectId",
            $this->accessToken);
    }

}

function jsonApi(): JsonApi
{
    if (empty($GLOBALS['jsonApi'])) {
        $GLOBALS['jsonApi'] = new JsonApi(AppInstance::get()->accessToken);
    }
    return $GLOBALS['jsonApi'];
}

//
//  Logging
//

const LOG_LEVELS = [
    'DEBUG' => 1,
    'INFO' => 2,
    'WARN' => 3,
    'ERROR' => 4
];

function log_message($level, $message)
{
    if (LOG_LEVELS[$level] >= LOG_LEVELS[LOG_LEVEL]) {
        $log_entry = sprintf(
            "[%s][%s] %s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message
        );

        // Пишем в stderr для Docker
        file_put_contents('php://stderr', $log_entry, FILE_APPEND);

        // Дополнительно в файл
        global $dirRoot;
        $logDir = $dirRoot . 'logs';
        @mkdir($logDir);
        file_put_contents($logDir . '/log.txt', $log_entry, FILE_APPEND);
    }
}

//
//  AppInstance state
//

$currentAppInstance = null;

class AppInstance
{

    const UNKNOWN = 0;
    const SETTINGS_REQUIRED = 1;
    const ACTIVATED = 100;

    var $appId;
    var $accountId;
    var $infoMessage;
    var $store;

    var $accessToken;

    var $status = AppInstance::UNKNOWN;

    static function get(): AppInstance
    {
        $app = $GLOBALS['currentAppInstance'];
        if (!$app) {
            throw new InvalidArgumentException("There is no current app instance context");
        }
        return $app;
    }

    public function __construct($appId, $accountId)
    {
        $this->appId = $appId;
        $this->accountId = $accountId;
    }

    function getStatusName()
    {
        switch ($this->status) {
            case self::SETTINGS_REQUIRED:
                return 'SettingsRequired';
            case self::ACTIVATED:
                return 'Activated';
        }
        return null;
    }

    function persist()
    {
        @mkdir('data');
        file_put_contents($this->filename(), serialize($this));
    }

    function delete()
    {
        @unlink($this->filename());
    }

    private function filename()
    {
        return self::buildFilename($this->appId, $this->accountId);
    }

    private static function buildFilename($appId, $accountId)
    {
        return $GLOBALS['dirRoot'] . "data/$appId.$accountId.app";
    }

    static function loadApp($accountId): AppInstance
    {
        return self::load(cfg()->appId, $accountId);
    }

    static function load($appId, $accountId): AppInstance
    {
        $data = @file_get_contents(self::buildFilename($appId, $accountId));
        if ($data === false) {
            $app = new AppInstance($appId, $accountId);
        } else {
            $app = unserialize($data);
        }
        $GLOBALS['currentAppInstance'] = $app;
        return $app;
    }

}
