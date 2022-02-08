<?php

declare(strict_types=1);

namespace Lamoda\RedisSentinel;

use PSRedis\Client;
use PSRedis\Client\Adapter\Predis\PredisClientFactory;

final class WithPasswordClientFactoryDecorator implements PredisClientFactory
{
    /** @var array */
    private $config;

    /** @var PredisClientFactory */
    private $decorated;

    public function __construct(array $config, PredisClientFactory $decorated)
    {
        $this->config = $config;
        $this->decorated = $decorated;
    }

    public function createClient($clientType, array $parameters = []): \Predis\Client
    {
        if ($clientType === Client::TYPE_REDIS && isset($this->config['password'])) {
            $parameters['password'] = $this->config['password'];
        }

        return $this->decorated->createClient($clientType, $parameters);
    }
}
