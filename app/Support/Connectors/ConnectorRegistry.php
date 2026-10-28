<?php

namespace App\Support\Connectors;

final class ConnectorRegistry
{
    /** @var array<string, Connector> */
    private array $connectors = [];

    public function __construct()
    {
        foreach (config('connectors.sources') as $class) {
            $connector = app($class);
            $this->connectors[$connector->code()] = $connector;
        }
    }

    public function has(string $code): bool
    {
        return isset($this->connectors[$code]);
    }

    public function get(string $code): Connector
    {
        return $this->connectors[$code] ?? throw new \InvalidArgumentException("No connector registered for {$code}");
    }

    /** @return array<int, string> */
    public function codes(): array
    {
        return array_keys($this->connectors);
    }
}
