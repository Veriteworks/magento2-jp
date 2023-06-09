<?php
declare(strict_types=1);

namespace CommunityEngineering\JapaneseInvoice\Block\Sales\PrintOrder;

use Magento\Sales\Model\Order;

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

        $this->initGiftWrapping();
        $this->initCashOnDelivery();
    }

    public function initCashOnDelivery()
    {
        $parent = $this->getParentBlock();
        $source = $parent->getSource();
        if ($source->getVwCodFee()) {
            $vwCodTaxAmount = $this->objectFactory->create()
                ->setData([
                    'code' => 'veriteworks_cod_excl_tax',
                    'strong' => false,
                    'value' => $parent->getSource()->getVwCodFee(),
                    'label' => __('Cash on delivery Fee'),
                ]);
            $parent->addTotalBefore($vwCodTaxAmount, 'tax');
        }
    }

    public function initGiftWrapping()
    {
        $parent = $this->getParentBlock();
        $source = $parent->getSource();
        if ($source->getGiftwrappingFee() || $this->getOrder()->getGiftwrappingFee()) {
            $giftwrappingfee = $source->getGiftwrappingFee() ?: $this->getOrder()->getGiftwrappingFee();
            $giftwrapping = $this->objectFactory->create()
                ->setData([
                    'code' => 'giftwrappingfee',
                    'strong' => false,
                    'value' => $giftwrappingfee,
                    'label' => __('Gift Wrapping Fee'),
                ]);
            $parent->addTotal($giftwrapping, 'shipping');
        }
    }

    /**
     * @return array
     */
    public function getTotals()
    {
        $parent = $this->getParentBlock();
        $source = $parent->getSource();
        if ($source->getOrder()) {
            $order = $this->getOrder();
        } else {
            /** @var Order $order */
            $order = $parent->getSource();
            $this->order = $order;
        }
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

        ksort($subtotals, SORT_NUMERIC);

        //todo: collect handling fees like cod, wrapping...
        //todo: re-calculate g.t. based discount
        return $subtotals;
    }

    public function getOrder()
    {
        $parent = $this->getParentBlock();
        $source = $parent->getSource();
        if ($source->getOrder()) {
            $order = $source->getOrder();
            $id = $order->getIncrementId();
            $order->loadByIncrementId($id);
        } else {
            $order = $this->order;
        }
        return $order;
    }

    public function getColspanData()
    {
        $parent = $this->getParentBlock();
        if (strstr($parent['type'], 'Creditmemo')) {
            return '7';
        } else {
            return '5';
        }
    }
}
