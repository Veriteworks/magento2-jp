<?php
declare(strict_types=1);

namespace CommunityEngineering\JapaneseInvoice\Block\Info;

use Magento\Framework\View\Element\Template;
use CommunityEngineering\JapaneseInvoice\Model\Config\RegistrationNumber as Config;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Order extends Template
{
    private $config;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        Template\Context $context,
        Config $config,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
    }

    public function getRegistrationNumber()
    {
        return $this->config->getNumber();
    }

    public function canShow()
    {
        return $this->config->showInOrder();
    }

    public function getStoreZip()
    {
        return $this->scopeConfig->getValue('general/store_information/postcode');
    }

    public function getStoreAddress()
    {
        $city = $this->scopeConfig->getValue('general/store_information/city');
        $street = $this->scopeConfig->getValue('general/store_information/street_line1');
        $street2 = $this->scopeConfig->getValue('general/store_information/street_line2');
        return $city.$street.$street2;
    }

    public function getStoreName()
    {
        return $this->scopeConfig->getValue('general/store_information/name');
    }
}
