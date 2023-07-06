<?php
declare(strict_types=1);

namespace CommunityEngineering\JapaneseInvoice\Model\Service\Invoice;

use \Magento\Sales\Model\Order;
use \Magento\Sales\Model\Order\Invoice;
use \Magento\Sales\Model\Order\Invoice\Item;

class Items
{
    /**
     * @var mixed
     */
    private $source;

    /**
     * Collect
     *
     * @param array $result
     * @return void
     */
    public function collect(array &$result)
    {
        $items = $this->source->getAllItems();
        //item row amount calculation
        /** @var Item $item */
        foreach ($items as $item) {
            $taxRate = (int)$item->getTaxPercent();
            if (!array_key_exists($taxRate, $result)) {
                $result[$taxRate] = [
                    'total' => 0,
                    'base_total' => 0,
                    'tax_amount' => 0,
                    'base_tax_amount' => 0
                ];
            }

            $result[$taxRate]['total'] += $item->getRowTotalInclTax() - $item->getDiscountAmount();
            $result[$taxRate]['base_total'] += $item->getBaseRowTotalInclTax() - $item->getBaseDiscountAmount();
            $result[$taxRate]['tax_amount'] += $item->getTaxAmount();
            $result[$taxRate]['base_tax_amount'] += $item->getBaseTaxAmount();

        }

        ksort($result, SORT_NUMERIC);
    }

    /**
     * Set source
     *
     * @param mixed $source source
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }
}
