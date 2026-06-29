<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Controller\Adminhtml\Health;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    public const ADMIN_RESOURCE = 'Amaresh_CommerceHealthCheck::health';

    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amaresh_CommerceHealthCheck::commerce_health');
        $resultPage->getConfig()->getTitle()->prepend(__('Commerce Health'));

        return $resultPage;
    }
}
