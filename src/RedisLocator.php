<?php

declare(strict_types=1);

namespace Lamoda\RedisSentinel;

use PSRedis\Client;
use PSRedis\Client\Adapter\Predis\PredisClientCreator;
use PSRedis\Client\Adapter\PredisClientAdapter;
use PSRedis\MasterDiscovery;

class RedisLocator
{
    /** @var array */
    private $redisConfig;

    /** @var array */
    private $redisSentinelConfig;

    /** @var MasterDiscovery */
    private $masterDiscovery;

    public function __construct(array $redisConfig, array $redisSentinelConfig)
    {
        $this->redisConfig = $redisConfig;
        $this->redisSentinelConfig = $redisSentinelConfig;

        $this->masterDiscovery = $this->initMasterDiscovery();
    }

    public function getRedisConfig(): RedisConfig
    {
        $redisConfig = $this->redisConfig;

        if ($this->masterDiscovery) {
            /** @var Client $master */
            $master = $this->masterDiscovery->getMaster();
            $redisConfig['host'] = $master->getIpAddress();
            $redisConfig['port'] = (int) $master->getPort();
        }

        return new RedisConfig($redisConfig);
    }

    private function initMasterDiscovery(): ?MasterDiscovery
    {
        $urls = trim((string) ($this->redisSentinelConfig['url'] ?? ''));
        if (empty($urls)) {
            return null;
        }

        $urls = preg_split('/[\s,;]+/', $urls);
        $masterDiscovery = new MasterDiscovery($this->redisSentinelConfig['redisName']);
        $backoffStrategy = new MasterDiscovery\BackoffStrategy\Incremental(100, 1.5);
        $backoffStrategy->setMaxAttempts(10);
        $masterDiscovery->setBackoffStrategy($backoffStrategy);
        foreach ($urls as $singleSentinelUrl) {
            $url = parse_url($singleSentinelUrl);
            if (false === $url) {
                throw new \InvalidArgumentException("Invalid Sentinel URL: $singleSentinelUrl");
            }
            $host = $url['host'] ?? $url['path'];
            $port = $url['port'] ?? 26379;

            $redisClientAdapter = $this->getRedisClientAdapter();
            $sentinel = new Client($host, $port, $redisClientAdapter);

            $masterDiscovery->addSentinel($sentinel);
        }

        return $masterDiscovery;
    }

    private function getRedisClientAdapter(): PredisClientAdapter
    {
        $clientFactory = new WithPasswordClientFactoryDecorator($this->redisConfig, new PredisClientCreator());

        return new PredisClientAdapter($clientFactory, Client::TYPE_SENTINEL);
    }
}
