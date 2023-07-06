<?php
declare(strict_types=1);

namespace CommunityEngineering\JapaneseInvoice\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class RegistrationNumber
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
     * Return registration number
     *
     * @return string
     */
    public function getNumber(): string
    {
        $configuredValue = $this->scopeConfig->getValue(
            'general/store_information/registration_number',
            ScopeInterface::SCOPE_WEBSITE
        );
        return $configuredValue;
    }

    /**
     * Get show in order view flag
     *
     * @return int
     */
    public function showInOrder(): int
    {
        $configuredValue = $this->scopeConfig->getValue(
            'tax/japanese_invoice/show_in_order',
            ScopeInterface::SCOPE_WEBSITE
        );
        return (int)$configuredValue;
    }

    /**
     * Get show in invoice view flag
     *
     * @return int
     */
    public function showInInvoice(): int
    {
        $configuredValue = $this->scopeConfig->getValue(
            'tax/japanese_invoice/show_in_invoice',
            ScopeInterface::SCOPE_WEBSITE
        );
        return (int)$configuredValue;
    }

    /**
     * Get show in creditmemo view flag
     *
     * @return int
     */
    public function showInCreditmemo(): int
    {
        $configuredValue = $this->scopeConfig->getValue(
            'tax/japanese_invoice/show_in_creditmemo',
            ScopeInterface::SCOPE_WEBSITE
        );
        return (int)$configuredValue;
    }
}
