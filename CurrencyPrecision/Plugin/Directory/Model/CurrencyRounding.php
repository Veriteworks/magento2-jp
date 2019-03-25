<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace CommunityEngineering\CurrencyPrecision\Plugin\Directory\Model;

use Magento\Directory\Model\PriceCurrency;
use Magento\Store\Model\StoreManager;
use CommunityEngineering\CurrencyPrecision\Model\CurrencyRounding as Model;

/**
 * Replace standard rounding method with rounding based on currency precision and with configured rounding method.
 */
class CurrencyRounding
{
    /**
     * @var CurrencyRounding
     */
    private $model;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @param Model $model
     * @param StoreManager $storeManager
     */
    public function __construct(
        Model $model,
        StoreManager $storeManager
    ) {
        $this->model = $model;
        $this->storeManager = $storeManager;
    }

    /**
     * Override original method to apply correct rounding logic.
     *
     * @param PriceCurrency $priceCurrency
     * @param \Closure $proceed
     * @param float $amount
     * @param string $scope
     * @param string $currency
     * @param int $precision
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundConvertAndRound(
        PriceCurrency $priceCurrency,
        \Closure $proceed,
        $amount,
        $scope = null,
        $currency = null,
        $precision = PriceCurrency::DEFAULT_PRECISION
    ) {
        $targetCurrency = $priceCurrency->getCurrency($scope, $currency);
        $convertedAmount = $this->storeManager->getStore($scope)->getBaseCurrency()->convert($amount, $targetCurrency);
        $roundedAmount = $this->round($targetCurrency->getCode(), (float)$convertedAmount);
        return $roundedAmount;
    }

    /**
     * Override original method to apply correct rounding logic.
     *
     * @param PriceCurrency $priceCurrency Price Currency
     * @param \Closure $proceed Closure
     * @param float $amount Price
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundRound(
        PriceCurrency $priceCurrency,
        \Closure $proceed,
        $amount
    ) {
        $currencyCode = $priceCurrency->getCurrency()->getCode();
        $roundedAmount = $this->round($currencyCode, (float)$amount);
        return $roundedAmount;
    }

    /**
     * Round currency using rounding service.
     *
     * @param string $currencyCode
     * @param float $amount
     * @return float
     */
    private function round(string $currencyCode, float $amount): float
    {
        $rounded = $this->model->round($currencyCode, $amount);
        return $rounded;
    }
}
