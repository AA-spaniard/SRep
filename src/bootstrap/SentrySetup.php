<?php

namespace src\bootstrap;

class SentrySetup
{
    private bool $isEnabled = false;

    public function setup(): void
    {
        $dsn = \getenv('SENTRY_DSN');
        if (!$dsn) {
            return;
        }

        $basePath = \dirname(__DIR__);
        \Sentry\init([
            'dsn' => $dsn,
            'environment' => \getenv('APP_ENV'),
            'prefixes' => [$basePath],
            'attach_stacktrace' => true,
        ]);
        $this->isEnabled = true;
    }

    public function configureScope(array $source, $requestId): void
    {
        if (!$this->isEnabled) {
            return;
        }

        \Sentry\configureScope(static function (\Sentry\State\Scope $scope) use ($source, $requestId): void {
            $scope->setUser([
                'id' => $source['profile']['user_id'],
            ]);
            $scope->setTag('reportType', $source['source']['type']);
            $scope->setTag('locale', $source['profile']['locale']);
            $scope->setTag('barcode', $source['source']['barcode']);
            $scope->setTag('requestId', $requestId);
            if (isset($source['source']['is_b2c'])) {
                $scope->setTag('b2what', $source['source']['is_b2c'] ? 'b2c' : 'b2b');
            }
            if (isset($data['profile']['office_region'])) {
                $scope->setTag('office_region', $data['profile']['office_region']);
            }

            $sectionsLine = \implode(',',
                \array_map(
                    function ($section) {
                        return $section['id'];
                    },
                    \array_filter(
                        $source['template']['blocks'],
                        function ($section) {
                            return $section['render'];
                        }
                    )
                )
            );
            $scope->setExtra('sections', $sectionsLine);
        });
    }
}
