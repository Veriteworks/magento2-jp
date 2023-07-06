<?php
declare(strict_types=1);

namespace CommunityEngineering\JapaneseInvoice\Block\Sales\Invoice;

use \Magento\Sales\Model\Order;
use \Magento\Framework\DataObject;
use \Magento\Framework\DataObjectFactory;

class Subtotal extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $objectFactory;

    /**
     * @var Order
     */
    private $order;

    /**
     * Constructor.
     *
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

    /**
     * Init Totals
     *
     * @return void
     */
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
     * Get Totals
     *
     * @return array
     */
    public function getTotals()
    {
        $parent = $this->getParentBlock();
        /** @var Order $order */
        $order = $parent->getSource()->getOrder();
        $this->order = $order;
        $allItems = $parent->getSource()->getAllItems();

        $subtotals = [];
        $discounts = [];

        //item row amount calculation
        /** @var Order\Invoice\Item $item */
        foreach ($allItems as $item) {
            $taxRate = (int)$item->getOrderItem()->getTaxPercent();
            if (!array_key_exists($taxRate, $subtotals)) {
                $subtotals[$taxRate] = [
                    'total' => 0,
                    'base_total' => 0,
                    'tax_amount' => 0,
                    'base_tax_amount' => 0
                ];
            }
            if ($item->getBaseDiscountAmount() > 0) {
                if (!array_key_exists($taxRate, $discounts)) {
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

        ksort($subtotals, SORT_NUMERIC);

        //todo: collect handling fees like cod, wrapping...
        //todo: re-calculate g.t. based discount
        return $subtotals;
    }

    /**
     * Get Order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }
}
