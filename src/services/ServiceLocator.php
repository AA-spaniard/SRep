<?php

namespace src\services;

class ServiceLocator
{
    public const BOOKMARKS = 'BOOKMARKS';
    public const LOGGER = 'LOGGER';

    private array $definitions = [];

    private array $services = [];

    public function setDefinition(string $serviceName, \Closure $serviceDefinition): void
    {
        $this->definitions[$serviceName] = $serviceDefinition;
    }

    public function getBookmarks(): Bookmarks
    {
        return $this->getService(self::BOOKMARKS);
    }

    public function getLogger(): Logger
    {
        return $this->getService(self::LOGGER);
    }

    private function getService(string $serviceName)
    {
        if (isset($this->services[$serviceName])) {
            return $this->services[$serviceName];
        }

        if (!isset($this->definitions[$serviceName])) {
            throw new \Exception("Service $serviceName not defined");
        }

        $definition = $this->definitions[$serviceName];
        $service = $definition();
        $this->services[$serviceName] = $service;

        return $service;
    }
}
