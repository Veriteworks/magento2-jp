<?php
declare(strict_types=1);

namespace CommunityEngineering\JapaneseInvoice\Block\Sales\Order;

use \Magento\Sales\Model\Order;
use \Magento\Framework\DataObject;
use \Magento\Framework\DataObjectFactory;

class Subtotal extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $objectFactory;

    private $order;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\DataObjectFactory $objectFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\DataObjectFactory $objectFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->objectFactory = $objectFactory;
    }

    public function initTotals()
    {
        $taxTotal = $this->objectFactory->create()
            ->setData([
                'code' => 'separated_subtotal',
                'block_name' => $this->getNameInLayout(),
                'area' => 'footer',
            ]);
        $parent = $this->getParentBlock();
        $parent->addTotal($taxTotal, 'last');
    }

    /**
     * @return array
     */
    public function getTotals()
    {
        $parent = $this->getParentBlock();
        /** @var Order $order */
        $order = $parent->getSource();
        $this->order = $order;
        $allItems = $order->getAllItems();

        $subtotals = [];
        $discounts = [];

        //item row amount calculation
        /** @var Order\Item $item */
        foreach($allItems as $item) {
            $taxRate = (int)$item->getTaxPercent();
            if (!array_key_exists($taxRate, $subtotals)) {
                $subtotals[$taxRate] = [
                    'total' => 0,
                    'base_total' => 0,
                    'tax_amount' => 0,
                    'base_tax_amount' => 0
                ];
            }
            if ($item->getBaseDiscountAmount() > 0) {
                if(!array_key_exists($taxRate, $discounts)) {
                    $discounts[$taxRate] = [
                        'discount' => 0,
                        'base_discount' => 0,
                    ];
                }
                $discounts[$taxRate]['discount'] += $item->getDiscountAmount();
                $discounts[$taxRate]['base_discount'] += $item->getBaseDiscountAmount();
            }
            $subtotals[$taxRate]['total'] += $item->getRowTotalInclTax() - $item->getDiscountAmount();
            $subtotals[$taxRate]['base_total'] += $item->getBaseRowTotalInclTax() - $item->getBaseDiscountAmount();
            $subtotals[$taxRate]['tax_amount'] += $item->getTaxAmount();
            $subtotals[$taxRate]['base_tax_amount'] += $item->getBaseTaxAmount();
        }


        //todo: collect handling fees like cod, wrapping...
        //todo: re-calculate g.t. based discount

        ksort($subtotals, SORT_NUMERIC);
        return $subtotals;
    }

    public function getOrder()
    {
        return $this->order;
    }
}
