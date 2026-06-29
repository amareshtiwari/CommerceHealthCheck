<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Model\Alert;

use Amaresh\CommerceHealthCheck\Model\Config;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class EmailNotifier
{
    private const EMAIL_TEMPLATE = 'commerce_health_check_alert';

    public function __construct(
        private readonly Config $config,
        private readonly TransportBuilder $transportBuilder,
        private readonly StateInterface $inlineTranslation,
        private readonly StoreManagerInterface $storeManager,
        private readonly AlertSubjectResolver $alertSubjectResolver,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param array<int, array{key: string, component: string, message: string}> $failures
     */
    public function send(array $failures): void
    {
        if (!$this->config->isEmailEnabled() || $failures === []) {
            return;
        }

        $recipient = $this->config->getEmailRecipient();

        if ($recipient === '') {
            return;
        }

        $lines = array_map(
            static fn (array $f): string => sprintf('%s: %s', $f['component'], $f['message']),
            $failures
        );

        $subject = $this->alertSubjectResolver->resolveSubject($failures);

        try {
            $this->inlineTranslation->suspend();

            $storeId = (int) $this->storeManager->getStore()->getId();

            $transport = $this->transportBuilder
                ->setTemplateIdentifier(self::EMAIL_TEMPLATE)
                ->setTemplateOptions(['area' => Area::AREA_ADMINHTML, 'store' => $storeId])
                ->setTemplateVars([
                    'subject' => $subject,
                    'alert_lines' => implode("\n", $lines),
                    'alert_html' => implode('<br/>', array_map('htmlspecialchars', $lines)),
                ])
                ->setFromByScope('general')
                ->addTo($recipient)
                ->getTransport();

            $transport->sendMessage();
        } catch (\Throwable $e) {
            $this->logger->error('Commerce Health Check email alert failed: ' . $e->getMessage());
        } finally {
            $this->inlineTranslation->resume();
        }
    }
}
