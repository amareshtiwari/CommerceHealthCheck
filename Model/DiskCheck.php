<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Model;

use Amaresh\CommerceHealthCheck\Api\CheckerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class DiskCheck implements CheckerInterface
{
    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly Config $config
    ) {
    }

    public function execute(): array
    {
        try {
            $path = $this->directoryList->getRoot();
            $freeBytes = disk_free_space($path);

            if ($freeBytes === false) {
                return [
                    'status' => false,
                    'message' => 'Disk Space Critical',
                ];
            }

            $freeGb = round($freeBytes / (1024 * 1024 * 1024), 2);

            if ($freeBytes < $this->config->getDiskCriticalBytes()) {
                return [
                    'status' => false,
                    'message' => sprintf('Disk Space Critical (%s GB free)', $freeGb),
                ];
            }

            if ($freeBytes < $this->config->getDiskWarningBytes()) {
                return [
                    'status' => false,
                    'message' => sprintf('Disk Space Warning (%s GB free)', $freeGb),
                ];
            }

            return [
                'status' => true,
                'message' => 'Disk Space Healthy',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => 'Disk Space Critical',
            ];
        }
    }
}
