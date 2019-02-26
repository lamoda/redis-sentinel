Lamoda redis sentinel
=====================

[![Build Status](https://travis-ci.org/lamoda/redis-sentinel.svg?branch=master)](https://travis-ci.org/lamoda/redis-sentinel)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/lamoda/redis-sentinel/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lamoda/redis-sentinel/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/lamoda/redis-sentinel/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/lamoda/redis-sentinel/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/lamoda/redis-sentinel/badges/build.png?b=master)](https://scrutinizer-ci.com/g/lamoda/redis-sentinel/build-status/master)

Redis configuration wrapper for use with redis sentinel or plain redis server.
Used to get connection settings for sentinel or plain old redis if sentinel is not defined (say for dev/test environment).

### Usage example

```php
use Lamoda\RedisSentinel\RedisLocator;

$redisLocator = new RedisLocator(
    // plain redis:
    [
        'protocol' => 'tcp',
        'host' => 'redis-host',
        'port' => 6379,
        'dbindex' => 0,
        'connectionName' => uniqid('client-app', true),
    ],
    // redis sentinel:
    [
        'url' => 'redis-sentinel1:26379; redis-sentinel2:26379',
        'redisName' => 'mastername',
    ]
);

// Discover current sentinel master:
$redisConfig = $redisLocator->getRedisConfig();

$redis = new \Redis();
$redis->connect($redisConfig->getHost(), $redisConfig->getPort());
$redis->client('setname', $redisConfig->getConnectionName());
$redis->select($redisConfig->getDbIndex());
```

Or if you don't have sentinel:

```php
$redisLocator = new RedisLocator(
    // plain redis:
    [
        'protocol' => 'tcp',
        'host' => 'redis-host',
        'port' => 6379,
        'dbindex' => 0,
        'connectionName' => uniqid('client-app', true),
    ],
    // redis sentinel:
    [
        'url' => '',
        'redisName' => 'mastername',
    ]
);

// Return plain redis config:
$redisConfig = $redisLocator->getRedisConfig();
```

### docker-compose for local usage

You can use docker-compose files to ease local sentinel usage & testing.

To start redis-sentinel containers:

```bash
docker-compose -f docker/docker-compose.yml up -d
```

Or using make:

```bash
make up
# ...
make down
```
