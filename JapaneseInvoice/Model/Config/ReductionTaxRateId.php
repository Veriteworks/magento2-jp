<?php
declare(strict_types=1);

namespace CommunityEngineering\JapaneseInvoice\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ReductionTaxRateId
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Return configured invoice mode
     *
     * @return int
     */
    public function getId(): int
    {
        $configuredValue = (int)$this->scopeConfig->getValue(
            'tax/japanese_invoice/reduction_tax_rate',
            ScopeInterface::SCOPE_WEBSITE
        );
        return $configuredValue;
    }
}
