<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const XML_PATH_MODULE_ENABLED = 'commerce_health_check/general/module_enabled';
    public const XML_PATH_ALERTS_ENABLED = 'commerce_health_check/general/alerts_enabled';
    public const XML_PATH_ALERT_CHECKS = 'commerce_health_check/general/alert_checks';
    public const XML_PATH_CRON_LOOKBACK = 'commerce_health_check/thresholds/cron_lookback_minutes';
    public const XML_PATH_DISK_WARNING_GB = 'commerce_health_check/thresholds/disk_warning_gb';
    public const XML_PATH_DISK_CRITICAL_GB = 'commerce_health_check/thresholds/disk_critical_gb';
    public const XML_PATH_REQUIRED_CONSUMERS = 'commerce_health_check/consumers/required_consumers';
    public const XML_PATH_INTEGRATION_ENDPOINTS = 'commerce_health_check/integrations/endpoints';
    public const XML_PATH_EMAIL_ENABLED = 'commerce_health_check/email/enabled';
    public const XML_PATH_EMAIL_RECIPIENT = 'commerce_health_check/email/recipient';
    public const XML_PATH_SLACK_ENABLED = 'commerce_health_check/slack/enabled';
    public const XML_PATH_SLACK_WEBHOOK = 'commerce_health_check/slack/webhook_url';
    public const XML_PATH_SLACK_CHANNEL = 'commerce_health_check/slack/channel';

    private const DEFAULT_CRON_LOOKBACK = 15;
    private const DEFAULT_DISK_WARNING_GB = 10;
    private const DEFAULT_DISK_CRITICAL_GB = 5;
    private const DEFAULT_REQUIRED_CONSUMERS = 'async.operations.all,inventory.source.items.cleanup';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isModuleEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_MODULE_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    public function isAlertsEnabled(): bool
    {
        return $this->isModuleEnabled()
            && $this->scopeConfig->isSetFlag(self::XML_PATH_ALERTS_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    public function isEmailEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_EMAIL_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    public function getEmailRecipient(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string[]
     */
    public function getAlertChecks(): array
    {
        $value = (string) $this->scopeConfig->getValue(self::XML_PATH_ALERT_CHECKS, ScopeInterface::SCOPE_STORE);

        if ($value === '') {
            return ['redis', 'cron', 'disk', 'integration'];
        }

        return array_filter(array_map('trim', explode(',', $value)));
    }

    public function getCronLookbackMinutes(): int
    {
        $value = (int) $this->scopeConfig->getValue(self::XML_PATH_CRON_LOOKBACK, ScopeInterface::SCOPE_STORE);

        return $value > 0 ? $value : self::DEFAULT_CRON_LOOKBACK;
    }

    public function getDiskWarningBytes(): int
    {
        $gb = (float) $this->scopeConfig->getValue(self::XML_PATH_DISK_WARNING_GB, ScopeInterface::SCOPE_STORE);

        if ($gb <= 0) {
            $gb = self::DEFAULT_DISK_WARNING_GB;
        }

        return (int) ($gb * 1024 * 1024 * 1024);
    }

    public function getDiskCriticalBytes(): int
    {
        $gb = (float) $this->scopeConfig->getValue(self::XML_PATH_DISK_CRITICAL_GB, ScopeInterface::SCOPE_STORE);

        if ($gb <= 0) {
            $gb = self::DEFAULT_DISK_CRITICAL_GB;
        }

        return (int) ($gb * 1024 * 1024 * 1024);
    }

    /**
     * @return string[]
     */
    public function getRequiredConsumers(): array
    {
        $value = (string) $this->scopeConfig->getValue(self::XML_PATH_REQUIRED_CONSUMERS, ScopeInterface::SCOPE_STORE);

        if ($value === '') {
            $value = self::DEFAULT_REQUIRED_CONSUMERS;
        }

        return array_filter(array_map('trim', explode(',', $value)));
    }

    /**
     * @return array<int, array{name: string, url: string}>
     */
    public function getIntegrationEndpoints(): array
    {
        $raw = (string) $this->scopeConfig->getValue(self::XML_PATH_INTEGRATION_ENDPOINTS, ScopeInterface::SCOPE_STORE);

        if ($raw === '') {
            return [];
        }

        $endpoints = [];

        foreach (preg_split('/\r\n|\r|\n/', $raw) ?: [] as $line) {
            $line = trim($line);

            if ($line === '' || !str_contains($line, '|')) {
                continue;
            }

            [$name, $url] = array_map('trim', explode('|', $line, 2));

            if ($name !== '' && $url !== '') {
                $endpoints[] = ['name' => $name, 'url' => $url];
            }
        }

        return $endpoints;
    }

    public function isSlackEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SLACK_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    public function getSlackWebhookUrl(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_SLACK_WEBHOOK, ScopeInterface::SCOPE_STORE);
    }

    public function getSlackChannel(): string
    {
        $channel = (string) $this->scopeConfig->getValue(self::XML_PATH_SLACK_CHANNEL, ScopeInterface::SCOPE_STORE);

        return $channel !== '' ? $channel : '#commerce-alerts';
    }
}
