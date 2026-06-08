<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Model;

use Amaresh\CommerceHealthCheck\Api\CheckerInterface;
use Magento\Framework\App\ResourceConnection;

class DatabaseCheck implements CheckerInterface
{
    public function __construct(
        private readonly ResourceConnection $resourceConnection
    ) {
    }

    public function execute(): array
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $result = $connection->fetchOne('SELECT 1');

            if ((string) $result !== '1') {
                return [
                    'status' => false,
                    'message' => 'Database Failed',
                ];
            }

            return [
                'status' => true,
                'message' => 'Database Connected',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => 'Database Failed',
            ];
        }
    }
}
