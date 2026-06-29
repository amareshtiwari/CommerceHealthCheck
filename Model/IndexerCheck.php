<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Model;

use Amaresh\CommerceHealthCheck\Api\CheckerInterface;
use Magento\Indexer\Model\Indexer;
use Magento\Indexer\Model\Indexer\CollectionFactory;

class IndexerCheck implements CheckerInterface
{
    public function __construct(
        private readonly CollectionFactory $indexerCollectionFactory
    ) {
    }

    public function execute(): array
    {
        try {
            $collection = $this->indexerCollectionFactory->create();
            $invalid = [];
            $working = [];

            foreach ($collection->getItems() as $indexer) {
                $status = $indexer->getStatus();

                if ($status === Indexer::STATUS_INVALID) {
                    $invalid[] = $indexer->getTitle();
                }

                if ($status === Indexer::STATUS_WORKING) {
                    $working[] = $indexer->getTitle();
                }
            }

            if ($invalid !== []) {
                return [
                    'status' => false,
                    'message' => 'Indexers Invalid: ' . implode(', ', $invalid),
                ];
            }

            if ($working !== []) {
                return [
                    'status' => false,
                    'message' => 'Indexers Working: ' . implode(', ', $working),
                ];
            }

            return [
                'status' => true,
                'message' => 'Indexers Valid',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => 'Indexers Invalid',
            ];
        }
    }
}
