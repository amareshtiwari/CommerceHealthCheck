<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Cron;

use Amaresh\CommerceHealthCheck\Model\Alert\AlertManager;

class HealthAlertCron
{
    public function __construct(
        private readonly AlertManager $alertManager
    ) {
    }

    public function execute(): void
    {
        $this->alertManager->process();
    }
}
