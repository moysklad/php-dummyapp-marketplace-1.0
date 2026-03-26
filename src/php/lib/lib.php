<?php

use \Firebase\JWT\JWT;

require_once __DIR__ . '/jwt.lib.php';

//
//  Config
//

class AppConfig
{
    public string $appId = '';
    public string $appUid = '';
    public string $secretKey = '';
    public string $appBaseUrl = '';

    public string $moyskladVendorApiEndpointUrl = 'https://apps-api.moysklad.ru/api/vendor/1.0';
    public string $moyskladJsonApiEndpointUrl = 'https://api.moysklad.ru/api/remap/1.2';

    public function __construct(array $cfg)
    {
        foreach ($cfg as $k => $v) {
            if (!property_exists($this, $k)) {
                continue;
            }

            $this->$k = ($v === null || $v === false) ? '' : (string)$v;
        }
    }
}

$cfg = new AppConfig(require(__DIR__ . '/../config.php'));

function dataDir(): string
{
    return __DIR__ . '/../data';
}

function cfg(): AppConfig
{
    return $GLOBALS['cfg'];
}

//
//  Session-based user context storage
//  DEMO ONLY: serves as an example for contextKey/session flow.
//  Do not use as-is in production without hardening.
//

const USER_CONTEXT_SESSION_KEY = 'userContext';
const USER_CONTEXT_HISTORY_LIMIT = 10;
const BACKEND_CONTEXT_TOKEN_TTL_SECONDS = 900;
const BACKEND_CONTEXT_TOKEN_AUDIENCE = 'php-demo-backend';
const BACKEND_CONTEXT_TOKEN_PURPOSE = 'backend-context';

function isHttpsRequest(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }

    $forwardedProto = strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));

    return $forwardedProto === 'https';
}

function ensureSessionStarted(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = isHttpsRequest();

    $sessionOptions = [
        'cookie_httponly' => true,
        // iframe в UI МоегоСклада открывается в third-party контексте:
        // для работы cookie сессии нужен SameSite=None (+ Secure на HTTPS).
        'cookie_samesite' => $isHttps ? 'None' : 'Lax',
    ];

    if ($isHttps) {
        $sessionOptions['cookie_secure'] = true;
    }

    session_start($sessionOptions);
}

function &userContextSessionBucket(): array
{
    ensureSessionStarted();

    if (!isset($_SESSION[USER_CONTEXT_SESSION_KEY]) || !is_array($_SESSION[USER_CONTEXT_SESSION_KEY])) {
        $_SESSION[USER_CONTEXT_SESSION_KEY] = [
            'byContextKey' => [],
            'lastContextKey' => null,
            'history' => [],
        ];
    }

    return $_SESSION[USER_CONTEXT_SESSION_KEY];
}

function saveUserContextToSession(string $contextKey, array $context): void
{
    $bucket = &userContextSessionBucket();
    $savedAt = time();

    $context['contextKey'] = $contextKey;
    $context['savedAt'] = $savedAt;

    $bucket['byContextKey'][$contextKey] = $context;
    $bucket['lastContextKey'] = $contextKey;
    $bucket['history'][] = [
        'contextKey' => $contextKey,
        'uid' => $context['uid'] ?? null,
        'fio' => $context['fio'] ?? null,
        'accountId' => $context['accountId'] ?? null,
        'savedAt' => $savedAt,
    ];

    if (count($bucket['history']) > USER_CONTEXT_HISTORY_LIMIT) {
        $bucket['history'] = array_slice($bucket['history'], -USER_CONTEXT_HISTORY_LIMIT);
    }
}

function loadUserContextFromSession(?string $contextKey = null): ?array
{
    $bucket = &userContextSessionBucket();

    if ($contextKey !== null) {
        return $bucket['byContextKey'][$contextKey] ?? null;
    }

    $lastContextKey = $bucket['lastContextKey'] ?? null;

    if ($lastContextKey !== null && isset($bucket['byContextKey'][$lastContextKey])) {
        return $bucket['byContextKey'][$lastContextKey];
    }

    return null;
}

function getUserContextHistoryFromSession(int $limit = 5): array
{
    $bucket = &userContextSessionBucket();
    $history = $bucket['history'] ?? [];

    if ($limit > 0) {
        $history = array_slice($history, -$limit);
    }

    return array_reverse($history);
}

function buildSessionContextMeta(int $historyLimit = 5): array
{
    ensureSessionStarted();

    return [
        'sessionId' => session_id(),
        'contextHistory' => getUserContextHistoryFromSession($historyLimit),
    ];
}

function buildBackendContextToken(array $context): string
{
    $accountId = trim((string)($context['accountId'] ?? ''));
    $uid = trim((string)($context['uid'] ?? ''));

    if ($accountId === '' || $uid === '') {
        throw new InvalidArgumentException('Context must include accountId and uid');
    }

    $issuedAt = time();

    $payload = [
        'iss' => cfg()->appUid,
        'aud' => BACKEND_CONTEXT_TOKEN_AUDIENCE,
        'iat' => $issuedAt,
        'exp' => $issuedAt + BACKEND_CONTEXT_TOKEN_TTL_SECONDS,
        'jti' => bin2hex(random_bytes(16)),
        'purpose' => BACKEND_CONTEXT_TOKEN_PURPOSE,
        'accountId' => $accountId,
        'uid' => $uid,
        'isAdmin' => (bool)($context['isAdmin'] ?? false),
    ];

    return JWT::encode($payload, cfg()->secretKey);
}

function getRequestHeadersSafe(): array
{
    if (function_exists('getallheaders')) {
        $headers = getallheaders();

        return is_array($headers) ? $headers : [];
    }

    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();

        return is_array($headers) ? $headers : [];
    }

    $headers = [];

    foreach ($_SERVER as $name => $value) {
        if (strpos($name, 'HTTP_') !== 0) {
            continue;
        }

        $normalizedName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
        $headers[$normalizedName] = $value;
    }

    return $headers;
}

function getRequestHeaderValue(string $name): ?string
{
    $headers = getRequestHeadersSafe();

    foreach ($headers as $headerName => $headerValue) {
        if (strcasecmp((string)$headerName, $name) === 0) {
            return trim((string)$headerValue);
        }
    }

    return null;
}

function getBearerTokenFromHeader(?string $authorizationHeader): ?string
{
    if (empty($authorizationHeader)) {
        return null;
    }

    $prefix = 'Bearer ';

    if (strncasecmp($authorizationHeader, $prefix, strlen($prefix)) !== 0) {
        return null;
    }

    $token = trim(substr($authorizationHeader, strlen($prefix)));

    return $token === '' ? null : $token;
}

function getBackendContextTokenFromRequest(): ?string
{
    $authHeader = getRequestHeaderValue('Authorization');
    $tokenFromHeader = getBearerTokenFromHeader($authHeader);

    if ($tokenFromHeader !== null) {
        return $tokenFromHeader;
    }

    $token = $_POST['contextToken'] ?? $_GET['contextToken'] ?? null;

    if ($token === null) {
        return null;
    }

    $token = trim((string)$token);

    return $token === '' ? null : $token;
}

function backendContextAudienceIsValid($audienceClaim): bool
{
    if (is_string($audienceClaim)) {
        return $audienceClaim === BACKEND_CONTEXT_TOKEN_AUDIENCE;
    }

    if (is_array($audienceClaim)) {
        return in_array(BACKEND_CONTEXT_TOKEN_AUDIENCE, $audienceClaim, true);
    }

    return false;
}

function decodeBackendContextToken(string $token): ?array
{
    try {
        $decoded = JWT::decode($token, cfg()->secretKey, ['HS256']);
    } catch (Exception $exception) {
        log_message('WARN', 'Context token decode failed: ' . $exception->getMessage());

        return null;
    }

    if (!is_object($decoded)) {
        log_message('WARN', 'Context token payload is not an object');

        return null;
    }

    if (($decoded->purpose ?? null) !== BACKEND_CONTEXT_TOKEN_PURPOSE) {
        log_message('WARN', 'Context token has invalid purpose');

        return null;
    }

    if (($decoded->iss ?? null) !== cfg()->appUid) {
        log_message('WARN', 'Context token has invalid issuer');

        return null;
    }

    if (!backendContextAudienceIsValid($decoded->aud ?? null)) {
        log_message('WARN', 'Context token has invalid audience');

        return null;
    }

    $accountId = trim((string)($decoded->accountId ?? ''));
    $uid = trim((string)($decoded->uid ?? ''));

    if ($accountId === '' || $uid === '') {
        log_message('WARN', 'Context token is missing required claims');

        return null;
    }

    return [
        'accountId' => $accountId,
        'uid' => $uid,
        'isAdmin' => (bool)($decoded->isAdmin ?? false),
    ];
}

function resolveBackendContextFromRequest(): ?array
{
    $contextToken = getBackendContextTokenFromRequest();

    if ($contextToken === null) {
        return null;
    }

    return decodeBackendContextToken($contextToken);
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

function makeHttpRequest(string $method, string $url, string $bearerToken, $data = null)
{
    $curl = curl_init($url);

    $headers = ['Authorization: Bearer ' . $bearerToken, 'Accept-Encoding: gzip'];

    if ($data) {
        $headers[] = 'Content-type: application/json';
    }

    log_message('DEBUG', "Request: $method $url" . print_r($headers, true) . print_r($data, true));

    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_ENCODING => '',
        CURLOPT_HEADER => true
    ];

    if ($method !== 'GET' && $data !== null) {
        $options[CURLOPT_POSTFIELDS] = is_array($data)
            ? http_build_query($data)
            : $data;
    }

    curl_setopt_array($curl, $options);

    $response = curl_exec($curl);
    $error = curl_error($curl);
    $info = curl_getinfo($curl);

    curl_close($curl);

    if ($error) {
        log_message('ERROR', "Response error: $error");

        return null;
    }

    $statusCode = (int)($info['http_code'] ?? 0);
    $headerSize = (int)($info['header_size'] ?? 0);
    $body = substr((string)$response, $headerSize);

    log_message('DEBUG', "Response: $method $url\n$response");

    if ($statusCode >= 400) {
        log_message('WARN', "HTTP $statusCode for $method $url");
    }

    if ($body === '') {
        return null;
    }

    $decoded = json_decode($body);

    if (json_last_error() !== JSON_ERROR_NONE) {
        log_message('WARN', "Failed to decode JSON for $method $url: " . json_last_error_msg());

        return null;
    }

    return $decoded;
}

$vendorApi = new VendorApi();

function vendorApi(): VendorApi
{
    return $GLOBALS['vendorApi'];
}

function buildJWT(): string
{
    $token = [
        'sub' => cfg()->appUid,
        'iat' => time(),
        'exp' => time() + 300,
        'jti' => bin2hex(random_bytes(32)),
    ];

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
        @mkdir(dataDir());
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
        return dataDir() . "/$appId.$accountId.app";
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
