<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Block\Adminhtml\Health;

use Amaresh\CommerceHealthCheck\Model\CheckerPool;
use Amaresh\CommerceHealthCheck\Model\Config;
use Amaresh\CommerceHealthCheck\Model\HealthScoreCalculator;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class Report extends Template
{
    protected $_template = 'Amaresh_CommerceHealthCheck::health/header.phtml';

    public function __construct(
        Context $context,
        private readonly CheckerPool $checkerPool,
        private readonly HealthScoreCalculator $healthScoreCalculator,
        private readonly Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function isModuleEnabled(): bool
    {
        return $this->config->isModuleEnabled();
    }

    public function getConfigUrl(): string
    {
        return $this->getUrl('adminhtml/system_config/edit', ['section' => 'commerce_health_check']);
    }

    public function getHealthScore(): int
    {
        if (!$this->isModuleEnabled()) {
            return 0;
        }

        return $this->healthScoreCalculator->calculate($this->checkerPool->runRaw());
    }

    public function getRefreshUrl(): string
    {
        return $this->getUrl('commercehealthcheck/health/index');
    }
}
