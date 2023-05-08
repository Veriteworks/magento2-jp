<?php
declare(strict_types=1);

namespace CommunityEngineering\JapaneseInvoice\Model\Service\Order;

use Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Api\TaxRateManagementInterface;
use Magento\Tax\Model\Config;
use Magento\Customer\Model\Group;

class Shipping
{

    /**
     * @var Order
     */
    private $source;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    private $taxRateManagement;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TaxRateManagementInterface $taxRateManagement
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->taxRateManagement = $taxRateManagement;
    }

    public function collect(array &$result)
    {
        $baseShippingAmount = $this->source->getBaseShippingAmount();
        $shippingAmount = $this->source->getShippingAmount();
        $baseShippingDiscount = $this->source->getBaseShippingDiscountAmount();
        $shippingDiscount = $this->source->getShippingDiscountAmount();

        $taxClassId = $this->getTaxClassId();
        $customerGroupId = $this->source->getCustomerGroupId() ?: Group::NOT_LOGGED_IN_ID;
        $taxRates = $this->taxRateManagement->getRatesByCustomerAndProductTaxClassId($customerGroupId, $taxClassId);
        //todo: get tax rate

    }

    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    private function getTaxClassId()
    {
        $configuredValue = $this->scopeConfig->getValue(
            Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS,
            ScopeInterface::SCOPE_WEBSITE
        );
        return $configuredValue;
    }
}
