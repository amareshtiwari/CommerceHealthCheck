<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Ui\DataProvider\Health;

use Amaresh\CommerceHealthCheck\Model\CheckerPool;
use Amaresh\CommerceHealthCheck\Model\Config;
use Magento\Ui\DataProvider\AbstractDataProvider;

class ListingDataProvider extends AbstractDataProvider
{
    private ?array $loadedData = null;

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        private readonly CheckerPool $checkerPool,
        private readonly Config $config,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData(): array
    {
        if ($this->loadedData !== null) {
            return $this->loadedData;
        }

        if (!$this->config->isModuleEnabled()) {
            return $this->loadedData = [
                'items' => [],
                'totalRecords' => 0,
            ];
        }

        $items = [];

        foreach ($this->checkerPool->run() as $index => $row) {
            $items[] = [
                'entity_id' => $index + 1,
                'component' => $row['component'],
                'status' => $row['health'],
                'details' => $row['message'],
            ];
        }

        return $this->loadedData = [
            'items' => $items,
            'totalRecords' => count($items),
        ];
    }
}
