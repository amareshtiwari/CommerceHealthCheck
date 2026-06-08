<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Model;

use Amaresh\CommerceHealthCheck\Api\CheckerInterface;
use Magento\Framework\HTTP\Client\Curl;

class IntegrationCheck implements CheckerInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly Curl $curl
    ) {
    }

    public function execute(): array
    {
        $endpoints = $this->config->getIntegrationEndpoints();

        if ($endpoints === []) {
            return [
                'status' => false,
                'message' => 'Integrations Not Configured',
            ];
        }

        $failed = [];

        foreach ($endpoints as $endpoint) {
            if (!$this->pingEndpoint($endpoint['url'])) {
                $failed[] = $endpoint['name'];
            }
        }

        if ($failed !== []) {
            return [
                'status' => false,
                'message' => 'Integration Failed: ' . implode(', ', $failed),
            ];
        }

        return [
            'status' => true,
            'message' => 'All Integrations Healthy',
        ];
    }

    private function pingEndpoint(string $url): bool
    {
        try {
            $this->curl->setTimeout(10);
            $this->curl->get($url);
            $status = $this->curl->getStatus();

            return $status >= 200 && $status < 400;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
