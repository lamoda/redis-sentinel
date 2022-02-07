<?php

declare(strict_types=1);

namespace Lamoda\RedisSentinel;

final class RedisConfig
{
    /** @var string */
    private $host;

    /** @var int */
    private $port;

    /** @var string */
    private $protocol;

    /** @var int */
    private $dbIndex;

    /** @var string|null */
    private $connectionName;

    /** @var string|null */
    private $password;

    public function __construct(array $options = [])
    {
        $options = array_merge([
            'host' => null,
            'port' => 6379,
            'protocol' => 'tcp',
            'dbIndex' => 0,
            'connectionName' => null,
            'password' => null,
        ], $options);

        $this->host = $options['host'];
        $this->port = (int) $options['port'];
        $this->protocol = $options['protocol'];
        $this->dbIndex = (int) $options['dbIndex'];
        $this->connectionName = $options['connectionName'];
        $this->password = $options['password'];
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function getDbIndex(): int
    {
        return $this->dbIndex;
    }

    public function getConnectionName(): ?string
    {
        return $this->connectionName;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}
