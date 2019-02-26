<?php

declare(strict_types=1);

namespace Lamoda\RedisSentinelTest;

use Lamoda\RedisSentinel\RedisConfig;
use PHPUnit\Framework\TestCase;

class RedisConfigTest extends TestCase
{
    public function testAll(): void
    {
        $redisConfig = new RedisConfig([
            'host' => 'redishost',
            'port' => 123,
            'protocol' => 'proto',
            'dbIndex' => 666,
            'connectionName' => 'client-123',
        ]);

        $this->assertEquals('redishost', $redisConfig->getHost());
        $this->assertEquals(123, $redisConfig->getPort());
        $this->assertEquals(666, $redisConfig->getDbIndex());
        $this->assertEquals('proto', $redisConfig->getProtocol());
        $this->assertEquals('client-123', $redisConfig->getConnectionName());
    }
}
