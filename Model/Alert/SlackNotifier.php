<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Model\Alert;

use Amaresh\CommerceHealthCheck\Model\Config;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

class SlackNotifier
{
    public function __construct(
        private readonly Config $config,
        private readonly Curl $curl,
        private readonly AlertSubjectResolver $alertSubjectResolver,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param array<int, array{key: string, component: string, message: string}> $failures
     */
    public function send(array $failures): void
    {
        if (!$this->config->isSlackEnabled() || $failures === []) {
            return;
        }

        $webhookUrl = $this->config->getSlackWebhookUrl();

        if ($webhookUrl === '') {
            return;
        }

        $lines = $this->alertSubjectResolver->resolveSlackLines($failures);
        $subject = $this->alertSubjectResolver->resolveSubject($failures);

        $payload = json_encode([
            'channel' => $this->config->getSlackChannel(),
            'username' => 'Commerce Health Check',
            'text' => $subject . "\n" . implode("\n", $lines),
            'icon_emoji' => ':warning:',
        ], JSON_THROW_ON_ERROR);

        try {
            $this->curl->setTimeout(10);
            $this->curl->addHeader('Content-Type', 'application/json');
            $this->curl->post($webhookUrl, $payload);

            if ($this->curl->getStatus() >= 400) {
                $this->logger->error(
                    'Commerce Health Check Slack alert failed with HTTP ' . $this->curl->getStatus()
                );
            }
        } catch (\Throwable $e) {
            $this->logger->error('Commerce Health Check Slack alert failed: ' . $e->getMessage());
        }
    }
}
