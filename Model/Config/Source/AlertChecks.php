<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AlertChecks implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'redis', 'label' => __('Redis')],
            ['value' => 'cron', 'label' => __('Cron')],
            ['value' => 'disk', 'label' => __('Disk Space')],
            ['value' => 'integration', 'label' => __('Integrations')],
            ['value' => 'database', 'label' => __('Database')],
            ['value' => 'opensearch', 'label' => __('OpenSearch')],
            ['value' => 'consumer', 'label' => __('Queue Consumers')],
            ['value' => 'indexer', 'label' => __('Indexers')],
        ];
    }
}
