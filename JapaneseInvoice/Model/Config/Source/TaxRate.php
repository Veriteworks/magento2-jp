<?php
declare(strict_types=1);

namespace CommunityEngineering\JapaneseInvoice\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection;
use Magento\Tax\Model\ResourceModel\Calculation\Rate\CollectionFactory;

class TaxRate implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $rateCollection;

    /**
     * Constructor.
     *
     * @param CollectionFactory $rateCollection
     */
    public function __construct(
        CollectionFactory $rateCollection
    ) {
        $this->rateCollection = $rateCollection;
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        /** @var Collection $collection */
        $collection = $this->rateCollection->create();
        $rates = $collection->toOptionArray();

        return $rates;
    }
}
