<?php

namespace src\services;

use Sentry\Severity;

class Logger
{
    public const EVENT_REQUEST = 'request';
    public const EVENT_RESPONSE = 'response';

    private const LEVEL_INFO = 'info';
    private const LEVEL_WARN = 'warn';
    private const LEVEL_ERROR = 'error';

    private string $requestId = '';

    private array $sourceTags = [];

    private $stdout;
    private $stderr;

    public function __construct()
    {
        $this->stdout = \fopen('php://stdout', 'w');
        $this->stderr = \fopen('php://stderr', 'w');
    }

    public function info(string $message, array $contextData = []): void
    {
        $this->log(
            level: self::LEVEL_INFO,
            message: $message,
            contextData: $contextData
        );
    }

    public function warn(string $message, array $contextData = []): void
    {
        \Sentry\captureMessage($message, new Severity('warning'));
        $this->log(
            level: self::LEVEL_WARN,
            message: $message,
            contextData: $contextData
        );
    }

    public function error(string $message, array $contextData = []): void
    {
        \Sentry\captureMessage($message, new Severity('error'));
        $this->log(
            level: self::LEVEL_ERROR,
            message: $message,
            contextData: $contextData
        );
    }

    public function logEvent(string $message, string $type, array $contextData = []): void
    {
        $this->log(
            level: self::LEVEL_INFO,
            message: $message,
            contextData: $contextData,
            eventType: $type,
        );
    }

    public function logReportRequest($sourceData): void
    {
        $this->logEvent('PDF generation started', self::EVENT_REQUEST, [
            'sourceData' => $this->prepareSourceData($sourceData),
        ]);
    }

    public function captureException(\Throwable $exception): void
    {
        \Sentry\captureException($exception);
        $this->log(
            level: self::LEVEL_ERROR,
            message: (string) $exception,
            contextData: [],
        );
    }

    public function setup(): void
    {
        \set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            switch ($errno) {
                case \E_NOTICE:
                case \E_WARNING:
                case \E_STRICT:
                case \E_DEPRECATED:
                case \E_CORE_WARNING:
                case \E_COMPILE_WARNING:
                case \E_USER_DEPRECATED:
                case \E_USER_WARNING:
                case \E_USER_NOTICE:
                    $this->warn($errstr, [
                        'errno' => $errno,
                        'errfile' => $errfile,
                        'errline' => $errline,
                    ]);
                    break;
                default:
                    $this->error($errstr, [
                        'errno' => $errno,
                        'errfile' => $errfile,
                        'errline' => $errline,
                    ]);
            }

            // `return false` triggers default PHP error handler
            return false;
        });
    }

    public function configureScope(array $source, $requestId): void
    {
        $this->sourceTags = [
            'userId' => $source['profile']['user_id'],
            'reportType' => $source['source']['type'],
            'locale' => $source['profile']['locale'],
            'barcode' => $source['source']['barcode'],
            'template' => $source['template'],
        ];
        if (isset($source['source']['is_b2c'])) {
            $this->sourceTags['b2what'] = $source['source']['is_b2c'] ? 'b2c' : 'b2b';
        }
        if (isset($source['profile']['office_region'])) {
            $this->sourceTags['office_region'] = $source['profile']['office_region'];
        }

        $this->requestId = $requestId;
    }

    private function log(string $level, string $message, array $contextData, ?string $eventType = null): void
    {
        $logStringData = [
            'metadata' => [
                'type' => $eventType ?? 'other',
                'level' => $level,
                'deploy' => [
                    'project_namespace' => \getenv('CI_PROJECT_NAMESPACE'),
                    'project_name' => \getenv('CI_PROJECT_NAME'),
                    'branch' => \getenv('CI_COMMIT_REF_SLUG'),
                ],
                'x-request-id' => $this->requestId,
                'created_at' => $this->renderDateTime(),
            ],
            'headers' => \getallheaders(),
            'request' => [
                'method' => $_SERVER['REQUEST_METHOD'],
                'scheme' => $_SERVER['REQUEST_SCHEME'],
                'path' => $_SERVER['REQUEST_URI'],
                'query' => $_GET,
            ],
            'message' => $message,
            'context' => $contextData,
            'sourceTags' => $this->sourceTags,
        ];

        if ('1' === \getenv('ENABLE_RICH_JSON_LOGGING')) {
            $this->writeRichJson($level, $logStringData);
        } else {
            $this->writeBasic($level, $logStringData);
        }
    }

    private function prepareSourceData($sourceData): ?array
    {
        if ('1' !== \getenv('ENABLE_RICH_JSON_LOGGING') || !\is_array($sourceData)) {
            return null;
        }

        if (\isset($sourceData['profile'])) {
            $nameFields = ['firstname', 'lastname', 'middlename'];
            foreach ($nameFields as $field) {
                if (\isset($sourceData['profile'][$field])) {
                    $sourceData['profile'][$field] = 'HIDDEN';
                }
            }
        }

        return $sourceData;
    }

    private function writeRichJson(string $level, array $logStringData): void
    {
        $logString = \json_encode($logStringData);

        switch ($level) {
            case self::LEVEL_INFO:
                \fwrite($this->stdout, "$logString\n");
                break;
            default:
                \fwrite($this->stderr, "$logString\n");
        }
    }

    private function writeBasic(string $level, array $logStringData): void
    {
        $message = $logStringData['message'];
        $context = $logStringData['context'];
        $contextString = \count($context) > 0 ? \print_r($context, true) : '';
        $logString = "$message $contextString";

        switch ($level) {
            case self::LEVEL_INFO:
                // These hex codes make colors for nix console
                \fwrite($this->stdout, "\033[36mINFO\033[0m: $logString\n");
                break;
            case self::LEVEL_WARN:
                \fwrite($this->stderr, "\033[33mWARNING\033[0m: $logString\n");
                break;
            default:
                \fwrite($this->stderr, "\033[31mERROR\033[0m: $logString\n");
        }
    }

    private function renderDateTime(): string
    {
        $t = \microtime(true);
        $micro = \sprintf('%06d', ($t - \floor($t)) * 1000000);
        $d = new \DateTime(\gmdate('Y-m-d H:i:s.'.$micro, $t), new \DateTimeZone('UTC'));

        return $d->format("Y-m-d\TH:i:s.u\Z");
    }
}
