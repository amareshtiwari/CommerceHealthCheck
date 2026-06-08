<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Model;

use Amaresh\CommerceHealthCheck\Api\CheckerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\ScopeInterface;

class OpenSearchCheck implements CheckerInterface
{
    private const XML_PATH_ENGINE = 'catalog/search/engine';
    private const XML_PATH_OPENSEARCH_HOST = 'catalog/search/opensearch_server_hostname';
    private const XML_PATH_OPENSEARCH_PORT = 'catalog/search/opensearch_server_port';
    private const XML_PATH_ELASTICSEARCH_HOST = 'catalog/search/elasticsearch7_server_hostname';
    private const XML_PATH_ELASTICSEARCH_PORT = 'catalog/search/elasticsearch7_server_port';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly Curl $curl
    ) {
    }

    public function execute(): array
    {
        try {
            $engine = (string) $this->scopeConfig->getValue(self::XML_PATH_ENGINE, ScopeInterface::SCOPE_STORE);

            if (!in_array($engine, ['opensearch', 'elasticsearch7', 'elasticsearch8'], true)) {
                return [
                    'status' => false,
                    'message' => 'OpenSearch Failed',
                ];
            }

            $host = (string) $this->scopeConfig->getValue(
                $engine === 'opensearch' ? self::XML_PATH_OPENSEARCH_HOST : self::XML_PATH_ELASTICSEARCH_HOST,
                ScopeInterface::SCOPE_STORE
            );
            $port = (string) $this->scopeConfig->getValue(
                $engine === 'opensearch' ? self::XML_PATH_OPENSEARCH_PORT : self::XML_PATH_ELASTICSEARCH_PORT,
                ScopeInterface::SCOPE_STORE
            );

            if ($host === '') {
                return [
                    'status' => false,
                    'message' => 'OpenSearch Failed',
                ];
            }

            $port = $port !== '' ? $port : '9200';
            $url = sprintf('http://%s:%s/_cluster/health', $host, $port);

            $this->curl->setTimeout(5);
            $this->curl->get($url);

            if ($this->curl->getStatus() !== 200) {
                return [
                    'status' => false,
                    'message' => 'OpenSearch Failed',
                ];
            }

            $body = json_decode($this->curl->getBody(), true);

            if (!is_array($body) || !isset($body['status'])) {
                return [
                    'status' => false,
                    'message' => 'OpenSearch Failed',
                ];
            }

            $clusterStatus = strtolower((string) $body['status']);

            if ($clusterStatus !== 'green') {
                return [
                    'status' => false,
                    'message' => 'OpenSearch Failed',
                ];
            }

            return [
                'status' => true,
                'message' => 'OpenSearch Healthy',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => 'OpenSearch Failed',
            ];
        }
    }
}
