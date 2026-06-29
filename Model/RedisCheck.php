<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Model;

use Amaresh\CommerceHealthCheck\Api\CheckerInterface;
use Magento\Framework\App\CacheInterface;

class RedisCheck implements CheckerInterface
{
    private const CACHE_KEY = 'commerce_health_check_redis_probe';
    private const CACHE_VALUE = 'ok';

    public function __construct(
        private readonly CacheInterface $cache
    ) {
    }

    public function execute(): array
    {
        try {
            $this->cache->save(self::CACHE_VALUE, self::CACHE_KEY, [], 60);
            $loaded = $this->cache->load(self::CACHE_KEY);

            if ($loaded !== self::CACHE_VALUE) {
                return [
                    'status' => false,
                    'message' => 'Redis Failed',
                ];
            }

            $this->cache->remove(self::CACHE_KEY);

            return [
                'status' => true,
                'message' => 'Redis Connected',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => 'Redis Failed',
            ];
        }
    }
}
