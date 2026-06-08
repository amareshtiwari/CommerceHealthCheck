<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Model;

use Amaresh\CommerceHealthCheck\Api\CheckerInterface;
use Magento\Framework\App\ResourceConnection;

class CronCheck implements CheckerInterface
{
    public function __construct(
        private readonly ResourceConnection $resourceConnection,
        private readonly Config $config
    ) {
    }

    public function execute(): array
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $table = $this->resourceConnection->getTableName('cron_schedule');

            $select = $connection->select()
                ->from($table, ['cnt' => new \Zend_Db_Expr('COUNT(*)')])
                ->where('created_at >= ?', $this->getThresholdTime());

            $count = (int) $connection->fetchOne($select);

            if ($count > 0) {
                return [
                    'status' => true,
                    'message' => 'Cron Running',
                ];
            }

            return [
                'status' => false,
                'message' => 'Cron Delayed',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => 'Cron Delayed',
            ];
        }
    }

    private function getThresholdTime(): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->modify(sprintf('-%d minutes', $this->config->getCronLookbackMinutes()))
            ->format('Y-m-d H:i:s');
    }
}
