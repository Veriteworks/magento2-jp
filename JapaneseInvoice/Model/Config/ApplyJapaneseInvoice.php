<?php
declare(strict_types=1);

namespace CommunityEngineering\JapaneseInvoice\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ApplyJapaneseInvoice
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Constructor.
     *
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
    public function getMode(): int
    {
        $configuredValue = $this->scopeConfig->getValue(
            'tax/japanese_invoice/show_subtotal_by_tax_rate',
            ScopeInterface::SCOPE_WEBSITE
        );
        return $configuredValue;
    }
}
