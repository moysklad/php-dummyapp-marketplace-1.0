<?php

use \Firebase\JWT\JWT;

require_once 'jwt.lib.php';

//
//  Config
//

class AppConfig {
//    var $appId = "ac02196f-e0ea-4818-b142-991455ea62bc";
//    var $appUid = "dummyapp.lognex";
//    var $secretKey = 'xRmHEAsIRSQAQv556CTRtL51W9rrgTjgSJ48rL3UgRE2UscvqDxRbZFbSueQaO8FXJEIGIIdkGFW6eMwtJUB8TnciHcBeyGP5dgIURjLKaORAARrqDvg6hDSmbdFugzR';
    var $appId = "a90b71b7-1c57-4e69-8e8e-58e1476135da";
    var $appUid = "dummyapp2.lognex";
    var $secretKey = 'nAbioPF2HAuYvrYpOikD3LYnNTzkYGugXqRT74hUGD47BeLEY7Zo7rHM4EK0wcj4oSAycrDpbVYhO44XdmKYtTEKzepbO4g6LzfYfU7c1ILRTfcGJOPpJTMkV8mwltJx';

    var $moyskladVendorApiEndpointUrl = 'https://online-marketplace-2.testms.lognex.ru/api/vendor/1.0';
}

$cfg = new AppConfig();

function cfg(): AppConfig {
    return $GLOBALS['cfg'];
}

//
//  Vendor API
//

class VendorApi {

    // todo !!! add logging

    function context(string $contextKey) {
        return $this->request('POST', '/context/' . $contextKey);
    }

    function updateAppStatus(string $appId, string $accountId, string $status) {
        return $this->request('PUT',
            "/apps/$appId/$accountId/status",
            "{\"status\": \"$status\"}");
    }

    private function request(string $method, $path, $body = null) {
        $url = cfg()->moyskladVendorApiEndpointUrl . $path;
        loginfo("APP => MOYSKLAD", "Send: $method $url\n$body");


        $opts = $body
            ? array('http' =>
                array(
                    'method'  => $method,
                    'header'  => array('Authorization: Bearer ' . buildJWT(), 'Content-Type: application/json'),
                    'content' => json_encode($body)
                )
            )
            : array('http' =>
                array(
                    'method'  => $method,
                    'header'  => 'Authorization: Bearer ' . buildJWT()
                )
            );
        $context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);
        return json_decode($result);
    }

}

$vendorApi = new VendorApi();

function vendorApi(): VendorApi {
    return $GLOBALS['vendorApi']; // todo !!! implement
}

function buildJWT() {
    $token = array(
        "sub" => cfg()->appUid,
        "iat" => time(),
        "exp" => time() + 300,
        "jti" => bin2hex(random_bytes(32))
    );
    return JWT::encode($token, cfg()->secretKey);
}

//
//  Logging
//

function loginfo($name, $msg) {
    @mkdir('logs');
    file_put_contents('logs/log.txt', date(DATE_W3C) . ' [' . $name . '] '. $msg . "\n", FILE_APPEND);
}

//
//  AppInstance state
//

class AppInstance {

    const UNKNOWN = 0;
    const SETTINGS_REQUIRED = 1;
    const ACTIVATED = 100;

    var $appId;
    var $accountId;
    var $infoMessage;
    var $skladName;

    var $accessToken;

    var $status = AppInstance::UNKNOWN;

    public function __construct($appId, $accountId)
    {
        $this->appId = $appId;
        $this->accountId = $accountId;
    }

    function getStatusName() {
        switch ($this->status) {
            case self::SETTINGS_REQUIRED:
                return 'SettingsRequired';
            case self::ACTIVATED:
                return 'Activated';
        }
        return null;
    }

    function persist() {
        @mkdir('data');
        file_put_contents($this->filename(), serialize($this));
    }

    function delete() {
        @unlink($this->filename());
    }

    private function filename() {
        return self::buildFilename($this->appId, $this->accountId);
    }

    private static function buildFilename($appId, $accountId) {
        return "data/$appId.$accountId.app";
    }

    static function loadApp($accountId): AppInstance {
        return self::load(cfg()->appId, $accountId);
    }

    static function load($appId, $accountId): AppInstance {
        $data = @file_get_contents("data/$appId.$accountId.app");
        if ($data === false) {
            return new AppInstance($appId, $accountId);
        }
        return unserialize($data);
    }

}