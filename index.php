<?php

declare(strict_types=1);

require './vendor/autoload.php';
require 'lib/php80/tcpdf.php';

use src\AtlasReport;
use src\bootstrap\SentrySetup;
use src\services\Logger;
use src\Utils;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->loadEnv(__DIR__.'/.env');

$sentrySetup = new SentrySetup();
$sentrySetup->setup();

$logger = new Logger();
$logger->setup();

$requestId = $_SERVER['HTTP_X_REQUEST_ID'] ?? \uniqid('', true);
\header('X-Request-Id: '.$requestId);

$url = $_SERVER['REQUEST_URI'];
if (\preg_match('/^\/api\/v1\//', $url)) {
    require 'src/api/router.php';
    exit();
}
if ('GET' === $_SERVER['REQUEST_METHOD']) {
    \header('Location: /api/v1/input-example/list', true, 302);
    exit();
}

$dataSource = \file_get_contents('php://input');
// Temporary logging shiv for staging server debug
if ('1' === \getenv('IS_LOG_SOURCES')) {
    Utils::logDataSource($dataSource);
}
$sourceData = \json_decode($dataSource, true);

$sentrySetup->configureScope($sourceData, $requestId);
$logger->configureScope($sourceData, $requestId);
$logger->logReportRequest($sourceData);

try {
    new AtlasReport($sourceData, $logger);
    $logger->logEvent('PDF generation finished', Logger::EVENT_RESPONSE);
} catch (\Throwable $exception) {
    $logger->captureException($exception);

    \http_response_code(500);
    \header('Content-Type: application/json');
    $json = [
        'request' => [
            'id' => $requestId,
        ],
        'error' => [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
        ],
    ];
    echo \json_encode($json);
}
