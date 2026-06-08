<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Plugin\Backend\Model\Menu;

use Amaresh\CommerceHealthCheck\Model\Config;
use Magento\Backend\Model\Menu\Builder;

class BuilderPlugin
{
    private const MENU_ID = 'Amaresh_CommerceHealthCheck::commerce_health';

    public function __construct(
        private readonly Config $config
    ) {
    }

    /**
     * @param \Magento\Backend\Model\Menu $result
     * @return \Magento\Backend\Model\Menu
     */
    public function afterGetResult(Builder $subject, $result)
    {
        if (!$this->config->isModuleEnabled() && $result->get(self::MENU_ID)) {
            $result->remove(self::MENU_ID);
        }

        return $result;
    }
}
