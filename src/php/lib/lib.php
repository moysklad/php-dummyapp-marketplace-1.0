<?php

use \Firebase\JWT\JWT;

require_once __DIR__ . '/jwt.lib.php';

// Конфигурация

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

function escHtml($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function normalizeIsAdmin($rawIsAdmin): bool
{
    if (is_bool($rawIsAdmin)) {
        return $rawIsAdmin;
    }

    if (is_string($rawIsAdmin)) {
        return strtoupper(trim($rawIsAdmin)) === 'ALL';
    }

    return false;
}

// Хранение пользовательского контекста в сессии.
// DEMO: пример потока contextKey -> $_SESSION.

const USER_CONTEXT_SESSION_KEY = 'userContext';
const USER_CONTEXT_STACK_LIMIT = 10;
const USER_CONTEXT_SESSION_TTL_SECONDS = 7200;

function ensureSessionStarted(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $sessionOptions = [
        'gc_maxlifetime' => USER_CONTEXT_SESSION_TTL_SECONDS,
        'cookie_httponly' => true,
        'cookie_samesite' => 'None',
        'cookie_secure' => true,
    ];

    session_start($sessionOptions);
}

function &userContextSessionBucket(): array
{
    ensureSessionStarted();

    if (!isset($_SESSION[USER_CONTEXT_SESSION_KEY]) || !is_array($_SESSION[USER_CONTEXT_SESSION_KEY])) {
        $_SESSION[USER_CONTEXT_SESSION_KEY] = [
            'byContextKey' => [],
            'contextKeyStack' => [],
        ];
    }

    if (!isset($_SESSION[USER_CONTEXT_SESSION_KEY]['byContextKey']) || !is_array($_SESSION[USER_CONTEXT_SESSION_KEY]['byContextKey'])) {
        $_SESSION[USER_CONTEXT_SESSION_KEY]['byContextKey'] = [];
    }

    if (!isset($_SESSION[USER_CONTEXT_SESSION_KEY]['contextKeyStack']) || !is_array($_SESSION[USER_CONTEXT_SESSION_KEY]['contextKeyStack'])) {
        $_SESSION[USER_CONTEXT_SESSION_KEY]['contextKeyStack'] = [];
    }

    trimUserContextBucket($_SESSION[USER_CONTEXT_SESSION_KEY]);

    return $_SESSION[USER_CONTEXT_SESSION_KEY];
}

function saveUserContextToSession(string $contextKey, array $context): void
{
    $bucket = &userContextSessionBucket();

    $context['contextKey'] = $contextKey;

    $bucket['byContextKey'][$contextKey] = $context;

    $updatedStack = [];

    foreach ($bucket['contextKeyStack'] as $existingKey) {
        if ($existingKey !== $contextKey) {
            $updatedStack[] = $existingKey;
        }
    }

    $updatedStack[] = $contextKey;
    $bucket['contextKeyStack'] = $updatedStack;

    trimUserContextBucket($bucket);
}

function loadUserContextFromSession(string $contextKey): ?array
{
    $bucket = &userContextSessionBucket();
    $context = $bucket['byContextKey'][$contextKey] ?? null;

    return is_array($context) ? $context : null;
}

function getContextKeyFromRequest(): ?string
{
    $contextKey = $_POST['contextKey'] ?? $_GET['contextKey'] ?? null;

    if ($contextKey === null) {
        return null;
    }

    $contextKey = trim((string)$contextKey);

    return $contextKey === '' ? null : $contextKey;
}

function resolveBackendContextFromSession(): ?array
{
    $contextKey = getContextKeyFromRequest();

    if ($contextKey === null) {
        return null;
    }

    $context = loadUserContextFromSession($contextKey);

    if (!is_array($context)) {
        return null;
    }

    $accountId = trim((string)($context['accountId'] ?? ''));
    $uid = trim((string)($context['uid'] ?? ''));

    if ($accountId === '' || $uid === '') {
        return null;
    }

    return [
        'accountId' => $accountId,
        'uid' => $uid,
        'isAdmin' => normalizeIsAdmin($context['isAdmin'] ?? false),
    ];
}

function trimUserContextBucket(array &$bucket): void
{
    $contexts = $bucket['byContextKey'] ?? [];
    $rawStack = $bucket['contextKeyStack'] ?? [];

    if (!is_array($contexts)) {
        $contexts = [];
    }

    if (!is_array($rawStack)) {
        $rawStack = [];
    }

    $stack = [];
    $seen = [];

    foreach ($rawStack as $contextKey) {
        if (!is_string($contextKey) || $contextKey === '' || !array_key_exists($contextKey, $contexts) || isset($seen[$contextKey])) {
            continue;
        }

        $seen[$contextKey] = true;
        $stack[] = $contextKey;
    }

    foreach ($contexts as $contextKey => $_context) {
        if (!is_string($contextKey) || $contextKey === '') {
            continue;
        }

        if (!isset($seen[$contextKey])) {
            $seen[$contextKey] = true;
            $stack[] = $contextKey;
        }
    }

    if (count($stack) > USER_CONTEXT_STACK_LIMIT) {
        $stack = array_slice($stack, -USER_CONTEXT_STACK_LIMIT);
    }

    $validKeys = array_flip($stack);

    foreach (array_keys($contexts) as $contextKey) {
        if (!isset($validKeys[$contextKey])) {
            unset($contexts[$contextKey]);
        }
    }

    $bucket['byContextKey'] = $contexts;
    $bucket['contextKeyStack'] = $stack;
}

// Vendor API 1.0

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

// JSON API 1.2

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

// Логирование

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

        // Пишем логи в stderr для Docker.
        file_put_contents('php://stderr', $log_entry, FILE_APPEND);
    }
}

// Состояние AppInstance

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
