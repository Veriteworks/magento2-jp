<?php

namespace CommunityEngineering\JapaneseInvoice\Block\Sales\Order;

use CommunityEngineering\JapaneseInvoice\Model\Config\ReductionTaxRateId;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\ResourceModel\Order\Tax\Collection;
use Magento\Sales\Model\ResourceModel\Order\Tax\CollectionFactory;
use Magento\Tax\Api\TaxRateRepositoryInterface;

class ReductionRateSign extends Template
{
    /**
     * @var CollectionFactory
     */
    private $taxCollectionFactory;

    /**
     * @var TaxRateRepositoryInterface
     */
    private $taxRateRepository;

    /**
     * @var ReductionTaxRateId
     */
    private $config;

    public function __construct(
        CollectionFactory $collectionFactory,
        TaxRateRepositoryInterface $taxRateRepository,
        ReductionTaxRateId $config,
        Template\Context $context,
        array $data = []
    ) {
        $this->taxCollectionFactory = $collectionFactory;
        $this->taxRateRepository = $taxRateRepository;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function showReductionRateSign()
    {
        $result = '';
        $orderItem = $this->getParentBlock()->getItem();
        $reductionTaxId = $this->config->getId();
        if (!$reductionTaxId) {
            return $result;
        }
        $order = $orderItem->getOrder();
        /** @var Collection $collection */
        $collection = $this->taxCollectionFactory->create();
        //get sales order tax.
        $collection->addAttributeToFilter('order_id', $order->getId())
            ->addAttributeToFilter('percent', $orderItem->getTaxPercent())
            ->load();
        if ($collection->getSize() === 0) {
            return $result;
        }
        //get item tax code.
        $appliedTax = $collection->getFirstItem();
        $taxRate = $this->taxRateRepository->get($reductionTaxId);
        //compare both tax code.
        if ($taxRate->getCode() === $appliedTax->getData('code')) {
            //add "*" into product name for reducted tax product.
            $result .= '*';
        }

        return $result;
    }
}
