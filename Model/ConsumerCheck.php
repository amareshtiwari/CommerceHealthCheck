<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Model;

use Amaresh\CommerceHealthCheck\Api\CheckerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Shell;

class ConsumerCheck implements CheckerInterface
{
    public function __construct(
        private readonly ResourceConnection $resourceConnection,
        private readonly Shell $shell,
        private readonly Config $config
    ) {
    }

    public function execute(): array
    {
        try {
            if ($this->areRequiredConsumersActive()) {
                return [
                    'status' => true,
                    'message' => 'Queue Consumers Running',
                ];
            }

            return [
                'status' => false,
                'message' => 'Queue Consumers Stopped',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => 'Queue Consumers Stopped',
            ];
        }
    }

    private function areRequiredConsumersActive(): bool
    {
        $required = $this->config->getRequiredConsumers();

        if ($required === []) {
            return false;
        }

        if (PHP_OS_FAMILY !== 'Windows' && $this->hasAllConsumersInProcessList($required)) {
            return true;
        }

        return $this->hasSuccessfulConsumersRunner();
    }

    /**
     * @param string[] $required
     */
    private function hasAllConsumersInProcessList(array $required): bool
    {
        try {
            $output = $this->shell->execute('ps aux | grep "[q]ueue:consumers:run"');
        } catch (\Throwable $e) {
            return false;
        }

        foreach ($required as $consumer) {
            if (!str_contains($output, $consumer)) {
                return false;
            }
        }

        return true;
    }

    private function hasSuccessfulConsumersRunner(): bool
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('cron_schedule');

        $select = $connection->select()
            ->from($table, ['cnt' => new \Zend_Db_Expr('COUNT(*)')])
            ->where('job_code = ?', 'consumers_runner')
            ->where('status = ?', 'success')
            ->where('finished_at >= ?', $this->getThresholdTime());

        return (int) $connection->fetchOne($select) > 0;
    }

    private function getThresholdTime(): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->modify(sprintf('-%d minutes', $this->config->getCronLookbackMinutes()))
            ->format('Y-m-d H:i:s');
    }
}
