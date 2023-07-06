<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace CommunityEngineering\JapaneseInvoice\Model\Order\Pdf\Items\Creditmemo;

/**
 * Sales Order Creditmemo Pdf default items renderer
 */
class DefaultCreditmemo extends \Magento\Sales\Model\Order\Pdf\Items\Creditmemo\DefaultCreditmemo
{
    /**
     * Draw process
     *
     * @return void
     */
    public function draw()
    {
        $order = $this->getOrder();
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();
        $lines = [];

        // draw Product name
        $lines[0] = [
            [
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                'text' => $this->string->split(html_entity_decode($item->getName()), 35, true, true),
                'feed' => 35
            ]
        ];

        // draw SKU
        $lines[0][] = [
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            'text' => $this->string->split(html_entity_decode($this->getSku($item)), 17),
            'feed' => 255,
            'align' => 'right',
        ];

        // draw Total (ex)
        $lines[0][] = [
            'text' => $order->formatPriceTxt($item->getRowTotal()),
            'feed' => 330,
            'font' => 'bold',
            'align' => 'right',
        ];

        // draw Discount
        $lines[0][] = [
            'text' => $order->formatPriceTxt(-$item->getDiscountAmount()),
            'feed' => 380,
            'font' => 'bold',
            'align' => 'right',
        ];

        // draw QTY
        $lines[0][] = ['text' => $item->getQty() * 1, 'feed' => 445, 'font' => 'bold', 'align' => 'right'];

        // draw Total (inc)
        $subtotal = $item->getRowTotal() +
            $item->getTaxAmount() +
            $item->getDiscountTaxCompensationAmount() -
            $item->getDiscountAmount();
        $lines[0][] = [
            'text' => $order->formatPriceTxt($subtotal),
            'feed' => 530,
            'font' => 'bold',
            'align' => 'right',
        ];

        // draw Tax Rate
        $lines[0][] = [
            'text' => (int)$item->getOrderItem()->getTaxPercent() . '%',
            'feed' => 565,
            'font' => 'bold',
            'align' => 'right',
        ];

        // draw options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = [
                    'text' => $this->string->split($this->filterManager->stripTags($option['label']), 40, true, true),
                    'font' => 'italic',
                    'feed' => 35,
                ];

                // draw options value
                $printValue = isset(
                    $option['print_value']
                ) ? $option['print_value'] : $this->filterManager->stripTags(
                    $option['value']
                );
                $lines[][] = ['text' => $this->string->split($printValue, 30, true, true), 'feed' => 40];
            }
        }

        $lineBlock = ['lines' => $lines, 'height' => 20];

        $page = $pdf->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $this->setPage($page);
    }
}
