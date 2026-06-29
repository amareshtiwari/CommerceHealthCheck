<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Model\Alert;

use Amaresh\CommerceHealthCheck\Model\CheckerPool;
use Amaresh\CommerceHealthCheck\Model\Config;
use Magento\Framework\FlagManager;

class AlertManager
{
    private const FLAG_CODE = 'commerce_health_check_last_alert_state';

    public function __construct(
        private readonly CheckerPool $checkerPool,
        private readonly Config $config,
        private readonly EmailNotifier $emailNotifier,
        private readonly SlackNotifier $slackNotifier,
        private readonly FlagManager $flagManager
    ) {
    }

    public function process(): void
    {
        if (!$this->config->isModuleEnabled() || !$this->config->isAlertsEnabled()) {
            return;
        }

        $results = $this->checkerPool->run();
        $alertChecks = array_flip($this->config->getAlertChecks());
        $failures = [];

        foreach ($results as $result) {
            if ($result['status']) {
                continue;
            }

            if (!isset($alertChecks[$result['key']])) {
                continue;
            }

            $failures[] = [
                'key' => $result['key'],
                'component' => $result['component'],
                'message' => $result['message'],
            ];
        }

        if ($failures === []) {
            $this->saveState([]);
            return;
        }

        if (!$this->hasNewFailures($failures)) {
            return;
        }

        $this->emailNotifier->send($failures);
        $this->slackNotifier->send($failures);
        $this->saveState($failures);
    }

    /**
     * @param array<int, array{key: string, component: string, message: string}> $failures
     */
    private function hasNewFailures(array $failures): bool
    {
        $previous = $this->loadState();
        $previousKeys = array_column($previous, 'key');
        $currentKeys = array_column($failures, 'key');

        sort($previousKeys);
        sort($currentKeys);

        return $previousKeys !== $currentKeys;
    }

    /**
     * @return array<int, array{key: string, component: string, message: string}>
     */
    private function loadState(): array
    {
        $flag = $this->flagManager->getFlagData(self::FLAG_CODE);

        if (!is_string($flag) || $flag === '') {
            return [];
        }

        $decoded = json_decode($flag, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<int, array{key: string, component: string, message: string}> $failures
     */
    private function saveState(array $failures): void
    {
        $this->flagManager->saveFlag(
            self::FLAG_CODE,
            json_encode($failures, JSON_THROW_ON_ERROR)
        );
    }
}
