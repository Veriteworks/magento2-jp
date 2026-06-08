<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace CommunityEngineering\JapaneseRegion\Test\Unit\Model\ResourceModel\Directory\Region;

use CommunityEngineering\JapaneseRegion\Model\ResourceModel\Directory\Region\Collection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\DataObject;
use Psr\Log\LoggerInterface;

/**
 * Test sort order of Regions for ja_JP locale.
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Region collection
     *
     * @var Collection
     */
    private $collection;

    /**
     * Locale resolver
     *
     * @var ResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $localeResolverMock;

    /**
     * @inheritdoc
     */
    protected function setUp() :void
    {
        $entityFactoryMock = $this->createStub(EntityFactory::class);
        $loggerMock = $this->createStub(LoggerInterface::class);
        $fetchStrategyMock = $this->createStub(FetchStrategyInterface::class);
        $eventManagerMock = $this->createStub(ManagerInterface::class);
        $this->localeResolverMock = $this->createStub(ResolverInterface::class);
        $connectionMock = $this->createStub(Mysql::class);
        $resourceMock = $this->createStub(AbstractDb::class);

        $selectMock = $this->createStub(Select::class);
        $connectionMock->method('select')
            ->willReturn($selectMock);
        $resourceMock->method('getConnection')
            ->willReturn($connectionMock);
        $resourceMock->method('getTable')
            ->willReturnArgument(0);

        $this->collection = new Collection(
            $entityFactoryMock,
            $loggerMock,
            $fetchStrategyMock,
            $eventManagerMock,
            $this->localeResolverMock,
            $connectionMock,
            $resourceMock
        );
    }

    /**
     * Test for toOptionArray (Japanese)
     *
     * @return void
     * @throws \Exception
     */
    public function testToOptionArrayForJp()
    {
        $this->localeResolverMock
            ->method('getLocale')
            ->willReturn('ja_JP');

        $items = [
            [
                'name' => 'Hokkaido',
                'default_name' => '北海道',
                'region_id' => 1,
                'country_id' => 1,
            ],
            [
                'name' => 'Aomori',
                'default_name' => '青森県',
                'region_id' => 2,
                'country_id' => 1,
            ],
        ];
        foreach ($items as $itemData) {
            $this->collection->addItem(new DataObject($itemData));
        }

        $expectedResult = [
            [
                'label' => __('Please select a region, state or province.'),
                'value' => null,
                'title' => null,
            ],
            [
                'value' => 1,
                'title' => '北海道',
                'country_id' => 1,
                'label' => 'Hokkaido',
            ],
            [
                'value' => 2,
                'title' => '青森県',
                'country_id' => 1,
                'label' => 'Aomori',
            ],
        ];

        $this->assertEquals($expectedResult, $this->collection->toOptionArray());
    }

    /**
     * Test for toOptionArray (non-Japanese)
     *
     * @return void
     * @throws \Exception
     */
    public function testToOptionArrayForEn()
    {
        $this->localeResolverMock
            ->method('getLocale')
            ->willReturn('en_US');

        $items = [
            [
                'name' => 'Hokkaido',
                'default_name' => 'Hokkaido',
                'region_id' => 1,
                'country_id' => 1,
            ],
            [
                'name' => 'Aomori',
                'default_name' => 'Aomori',
                'region_id' => 2,
                'country_id' => 1,
            ],
        ];
        foreach ($items as $itemData) {
            $this->collection->addItem(new DataObject($itemData));
        }

        $expectedResult = [
            [
                'label' => __('Please select a region, state or province.'),
                'value' => null,
                'title' => null,
            ],
            [
                'value' => 1,
                'title' => 'Hokkaido',
                'country_id' => 1,
                'label' => 'Hokkaido',
            ],
            [
                'value' => 2,
                'title' => 'Aomori',
                'country_id' => 1,
                'label' => 'Aomori',
            ],
        ];

        $this->assertEquals($expectedResult, $this->collection->toOptionArray());
    }
}
