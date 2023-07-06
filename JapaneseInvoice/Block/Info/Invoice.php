<?php
declare(strict_types=1);

namespace CommunityEngineering\JapaneseInvoice\Block\Info;

use Magento\Framework\View\Element\Template;
use CommunityEngineering\JapaneseInvoice\Model\Config\RegistrationNumber as Config;

class Invoice extends Template
{
    /**
     * @var Config
     */
    private $config;

    /**
     * Constructor.
     *
     * @param Template\Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * Get Registration Number
     *
     * @return string
     */
    public function getRegistrationNumber()
    {
        return $this->config->getNumber();
    }

    /**
     * Can Show
     *
     * @return int
     */
    public function canShow()
    {
        return $this->config->showInInvoice();
    }
}
