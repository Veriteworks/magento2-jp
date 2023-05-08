<?php
declare(strict_types=1);

namespace CommunityEngineering\JapaneseInvoice\Block\Info;

use Magento\Framework\View\Element\Template;
use CommunityEngineering\JapaneseInvoice\Model\Config\RegistrationNumber as Config;

class Invoice extends Template
{
    private $config;

    public function __construct(
        Template\Context $context,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    public function getRegistrationNumber()
    {
        return $this->config->getNumber();
    }

    public function canShow()
    {
        return $this->config->showInInvoice();
    }
}
