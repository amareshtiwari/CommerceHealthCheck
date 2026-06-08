<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Model;

use Amaresh\CommerceHealthCheck\Api\CheckerInterface;

class CheckerPool
{
    /**
     * @var array<string, array{component: string, checker: CheckerInterface}>
     */
    private array $checkers;

    public function __construct(
        DatabaseCheck $databaseCheck,
        RedisCheck $redisCheck,
        OpenSearchCheck $openSearchCheck,
        CronCheck $cronCheck,
        ConsumerCheck $consumerCheck,
        IndexerCheck $indexerCheck,
        DiskCheck $diskCheck,
        IntegrationCheck $integrationCheck
    ) {
        $this->checkers = [
            'database' => ['component' => 'Database', 'checker' => $databaseCheck],
            'redis' => ['component' => 'Redis', 'checker' => $redisCheck],
            'opensearch' => ['component' => 'OpenSearch', 'checker' => $openSearchCheck],
            'cron' => ['component' => 'Cron', 'checker' => $cronCheck],
            'consumer' => ['component' => 'Queue Consumers', 'checker' => $consumerCheck],
            'indexer' => ['component' => 'Indexers', 'checker' => $indexerCheck],
            'disk' => ['component' => 'Disk Space', 'checker' => $diskCheck],
            'integration' => ['component' => 'Integrations', 'checker' => $integrationCheck],
        ];
    }

    /**
     * @return array<int, array{key: string, component: string, status: bool, message: string, health: string}>
     */
    public function run(): array
    {
        $results = [];

        foreach ($this->checkers as $key => $entry) {
            $check = $entry['checker']->execute();

            $results[] = [
                'key' => $key,
                'component' => $entry['component'],
                'status' => (bool) ($check['status'] ?? false),
                'message' => (string) ($check['message'] ?? ''),
                'health' => ($check['status'] ?? false) ? 'Healthy' : 'Unhealthy',
            ];
        }

        return $results;
    }

    /**
     * @return array<int, array{status: bool, message: string}>
     */
    public function runRaw(): array
    {
        $results = [];

        foreach ($this->checkers as $entry) {
            $results[] = $entry['checker']->execute();
        }

        return $results;
    }
}
