<?php

namespace CommunityEngineering\JapaneseInvoice\Plugin\Store\Information;

use CommunityEngineering\JapaneseInvoice\Model\Config\RegistrationNumber;
use Magento\Framework\DataObject;
use Magento\Store\Model\Information;
use Magento\Store\Model\Store;

class AddRegistrationNumber
{
    /**
     * @var RegistrationNumber
     */
    private $config;

    /**
     * @param RegistrationNumber $config
     */
    public function __construct(
        RegistrationNumber $config
    ) {
        $this->config = $config;
    }
    /**
     * Add registration number into store address data
     *
     * @param Information $subject
     * @param DataObject $result
     * @param Store $store
     * @return DataObject
     */
    public function afterGetStoreInformationObject(Information $subject, DataObject $result, Store $store): DataObject
    {
        $result['registration_number'] = $this->config->getNumber();
        return $result;
    }
}
