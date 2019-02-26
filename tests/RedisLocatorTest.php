<?php

declare(strict_types=1);

namespace Lamoda\RedisSentinelTest;

use Lamoda\RedisSentinel\RedisLocator;
use PHPUnit\Framework\TestCase;
use PSRedis\Client;
use PSRedis\Exception\ConnectionError;
use PSRedis\MasterDiscovery;

class RedisLocatorTest extends TestCase
{
    private $redisConfig = [
        'host' => 'redishost',
        'port' => 123,
        'protocol' => 'tcp',
        'dbIndex' => 666,
        'connectionName' => 'clientname',
    ];

    /**
     * @dataProvider dataGetRedisConfigWithSentinel
     */
    public function testGetRedisConfigWithSentinel(string $url, array $expectedSentinels): void
    {
        $redisLocator = new RedisLocator(
            $this->redisConfig,
            [
                'url' => $url,
                'redisName' => 'redis-name',
            ]
        );

        $classReflection = new \ReflectionClass(RedisLocator::class);
        $property = $classReflection->getProperty('masterDiscovery');
        $property->setAccessible(true);

        /** @var MasterDiscovery $masterDiscovery */
        $masterDiscovery = $property->getValue($redisLocator);

        $this->assertInstanceOf(MasterDiscovery::class, $masterDiscovery);
        $this->assertEquals('redis-name', $masterDiscovery->getName());
        $sentinels = $masterDiscovery->getSentinels();
        $this->assertCount(\count($expectedSentinels), $sentinels);
        foreach ($sentinels as $i => $sentinel) {
            /* @var Client $sentinel */
            $this->assertEquals($expectedSentinels[$i]['host'], $sentinel->getIpAddress());
            $this->assertEquals($expectedSentinels[$i]['port'], $sentinel->getPort());
        }

        $masterDiscoveryMock = $this->createPartialMock(MasterDiscovery::class, ['getMaster']);
        $masterDiscoveryMock->expects($this->once())
            ->method('getMaster')
            ->willReturn(reset($sentinels));

        $property->setValue($redisLocator, $masterDiscoveryMock);

        $redisConfig = $redisLocator->getRedisConfig();
        $this->assertEquals($expectedSentinels[0]['host'], $redisConfig->getHost());
        $this->assertEquals($expectedSentinels[0]['port'], $redisConfig->getPort());
        $this->assertEquals(666, $redisConfig->getDbIndex());
        $this->assertEquals('tcp', $redisConfig->getProtocol());
        $this->assertEquals('clientname', $redisConfig->getConnectionName());
    }

    public function dataGetRedisConfigWithSentinel(): array
    {
        return [
            'single host' => [
                'url' => 'redis-sentinel',
                'expectedSentinels' => [
                    [
                        'host' => 'redis-sentinel',
                        'port' => 26379,
                    ],
                ],
            ],
            'multiple hosts' => [
                'url' => '  redis-sentinel:12345; redis-sentinel2;redis-sentinel3:54545',
                'expectedSentinels' => [
                    [
                        'host' => 'redis-sentinel',
                        'port' => 12345,
                    ],
                    [
                        'host' => 'redis-sentinel2',
                        'port' => 26379,
                    ],
                    [
                        'host' => 'redis-sentinel3',
                        'port' => 54545,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataEmptySentinelConfig
     */
    public function testGetRedisConfigWithoutSentinel(array $sentinelConfig): void
    {
        $redisLocator = new RedisLocator($this->redisConfig, $sentinelConfig);

        $redisConfig = $redisLocator->getRedisConfig();
        $this->assertEquals('redishost', $redisConfig->getHost());
        $this->assertEquals(123, $redisConfig->getPort());
        $this->assertEquals(666, $redisConfig->getDbIndex());
        $this->assertEquals('tcp', $redisConfig->getProtocol());
        $this->assertEquals('clientname', $redisConfig->getConnectionName());
    }

    public function dataEmptySentinelConfig(): array
    {
        return [
            'empty array' => [
                'sentinelConfig' => [],
            ],
            'empty url' => [
                'sentinelConfig' => ['url' => ''],
            ],
            'false like from getenv() call' => [
                'sentinelConfig' => ['url' => false],
            ],
        ];
    }

    public function testShouldUseLazyConnectionToSentinelMaster(): void
    {
        $redisLocator = new RedisLocator(
            $this->redisConfig,
            [
                'url' => '127.0.0.1:1',
                'redisName' => 'unknown',
            ]
        );

        $this->expectException(ConnectionError::class);
        $config = $redisLocator->getRedisConfig();
    }

    public function testShouldThrowExceptionOnWrongAddress(): void
    {
        $url = '127.0.0.1:65536';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid Sentinel URL: $url");

        $redisLocator = new RedisLocator(
            $this->redisConfig,
            [
                'url' => $url,
                'redisName' => 'unknown',
            ]
        );
    }
}
