<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace CommunityEngineering\JapaneseInvoice\Model\Order\Pdf;

use Magento\Sales\Model\RtlTextHandler;
use CommunityEngineering\JapaneseInvoice\Block\Info\Order as OrderInfo;

class Creditmemo extends \Magento\Sales\Model\Order\Pdf\Creditmemo
{
    /**
     * @var RtlTextHandler
     */
    private $rtlTextHandler;

    /**
     * @var OrderInfo
     */
    private $orderInfo;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $appEmulation;

    /**
     * Constructor.
     *
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Sales\Model\Order\Pdf\Config $pdfConfig
     * @param \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory
     * @param \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param RtlTextHandler $rtlTextHandler
     * @param OrderInfo $orderInfo
     * @param array $data
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sales\Model\Order\Pdf\Config $pdfConfig,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\App\Emulation $appEmulation,
        RtlTextHandler $rtlTextHandler,
        OrderInfo $orderInfo,
        array $data = []
    ) {
        $this->rtlTextHandler = $rtlTextHandler;
        $this->orderInfo = $orderInfo;
        $this->appEmulation = $appEmulation;
        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystem,
            $pdfConfig,
            $pdfTotalFactory,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $addressRenderer,
            $storeManager,
            $appEmulation,
            $data
        );
    }

    /**
     * Draw table header for product items
     *
     * @param  \Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 30);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        //columns headers
        $lines[0][] = ['text' => __('Products'), 'feed' => 35];

        $lines[0][] = [
            'text' => $this->string->split(__('SKU'), 12, true, true),
            'feed' => 255,
            'align' => 'right',
        ];

        $lines[0][] = [
            'text' => $this->string->split(__('Total (ex)'), 12, true, true),
            'feed' => 330,
            'align' => 'right',
        ];

        $lines[0][] = [
            'text' => $this->string->split(__('Discount'), 12, true, true),
            'feed' => 380,
            'align' => 'right',
        ];

        $lines[0][] = [
            'text' => $this->string->split(__('Qty'), 12, true, true),
            'feed' => 445,
            'align' => 'right',
        ];

        $lines[0][] = [
            'text' => $this->string->split(__('Total (inc)'), 12, true, true),
            'feed' => 530,
            'align' => 'right',
        ];

        $lines[0][] = [
            'text' => $this->string->split(__('Tax Rate'), 12, true, true),
            'feed' => 565,
            'align' => 'right',
        ];

        $lineBlock = ['lines' => $lines, 'height' => 10];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    /**
     * Return PDF document
     *
     * @param  array $creditmemos
     * @return \Zend_Pdf
     */
    public function getPdf($creditmemos = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('creditmemo');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($creditmemos as $creditmemo) {
            if ($creditmemo->getStoreId()) {
                $this->appEmulation->startEnvironmentEmulation(
                    $creditmemo->getStoreId(),
                    \Magento\Framework\App\Area::AREA_FRONTEND,
                    true
                );
                $this->_storeManager->setCurrentStore($creditmemo->getStoreId());
            }
            $page = $this->newPage();
            $order = $creditmemo->getOrder();
            /* Add image */
            $this->insertLogo($page, $creditmemo->getStore());
            /* Add address */
            $this->insertAddress($page, $creditmemo->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_CREDITMEMO_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );
            /* Add document text and number */
            $this->insertDocumentNumber($page, __('Credit Memo # ') . $creditmemo->getIncrementId());
            /* Add table head */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($creditmemo->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
            /* Add totals */
            $this->insertTotals($page, $creditmemo);

            /* Add invoice tax details */
            $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $page->drawLine(25, $this->y, 570, $this->y);
            $this->y -= 20;
            $this->insertTaxTotals($page, $creditmemo);

            if ($creditmemo->getStoreId()) {
                $this->appEmulation->stopEnvironmentEmulation();
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * Insert order to pdf page.
     *
     * @param \Zend_Pdf_Page $page
     * @param \Magento\Sales\Model\Order $obj
     * @param bool $putOrderId
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function insertOrder(&$page, $obj, $putOrderId = true)
    {
        if ($obj instanceof \Magento\Sales\Model\Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof \Magento\Sales\Model\Order\Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->drawRectangle(25, $top, 570, $top - 100);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->setDocHeaderCoordinates([25, $top, 570, $top - 100]);
        $this->_setFontRegular($page, 10);

        if ($putOrderId) {
            $page->drawText(__('Order # ') . $order->getRealOrderId(), 35, $top -= 30, 'UTF-8');
            $top +=15;
        }

        $top -=30;
        $page->drawText(
            __('Order Date: ') .
            $this->_localeDate->formatDate(
                $this->_localeDate->scopeDate(
                    $order->getStore(),
                    $order->getCreatedAt(),
                    true
                ),
                \IntlDateFormatter::MEDIUM,
                false
            ),
            35,
            $top,
            'UTF-8'
        );

        /* Add Shipping Date */
        $top -=15;
        $shipmentCollection = $order->getShipmentsCollection();
        foreach ($shipmentCollection as $item) {
            $page->drawText(
                __('Shipping Date:') .
                $this->_localeDate->formatDate(
                    $this->_localeDate->scopeDate(
                        $order->getStore(),
                        $item['created_at'],
                        true
                    ),
                    \IntlDateFormatter::MEDIUM,
                    false
                ),
                35,
                $top,
                'UTF-8'
            );
        }

        /* Add Publish Date */
        $top +=45;
        $creditmemoCollection = $order->getCreditmemosCollection();
        foreach ($creditmemoCollection as $item) {
            $page->drawText(
                __('Publish Date:') .
                $this->_localeDate->formatDate(
                    $this->_localeDate->scopeDate(
                        $order->getStore(),
                        $item['created_at'],
                        true
                    ),
                    \IntlDateFormatter::MEDIUM,
                    false
                ),
                285,
                $top,
                'UTF-8'
            );
        }

        /* Add Publisher */
        $page->drawText(__('(Publisher)'), 285, $top -= 15, 'UTF-8');
        $page->drawText('〒' . $this->orderInfo->getStoreZip(), 285, $top -= 15, 'UTF-8');
        $page->drawText($this->orderInfo->getStoreAddress(), 285, $top -= 15, 'UTF-8');
        $page->drawText($this->orderInfo->getStoreName(), 285, $top -= 15, 'UTF-8');
        $page->drawText(
            __('Registration Number:') . $this->orderInfo->getRegistrationNumber(),
            285,
            $top -= 15,
            'UTF-8'
        );

        $top -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 275, $top - 25);
        $page->drawRectangle(275, $top, 570, $top - 25);

        /* Calculate blocks info */

        /* Billing Address */
        $billingAddress = $this->_formatAddress($this->addressRenderer->format($order->getBillingAddress(), 'pdf'));

        /* Payment */
        $paymentInfo = $this->_paymentData->getInfoBlock($order->getPayment())->setIsSecureMode(true)->toPdf();
        $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value) {
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress(
                $this->addressRenderer->format($order->getShippingAddress(), 'pdf')
            );
            $shippingMethod = $order->getShippingDescription();
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 12);
        $page->drawText(__('Sold to:'), 35, $top - 15, 'UTF-8');

        if (!$order->getIsVirtual()) {
            $page->drawText(__('Ship to:'), 285, $top - 15, 'UTF-8');
        } else {
            $page->drawText(__('Payment Method:'), 285, $top - 15, 'UTF-8');
        }

        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, $top - 25, 570, $top - 33 - $addressesHeight);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;

        foreach ($billingAddress as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $text[] = $this->rtlTextHandler->reverseRtlText($_value);
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }

        $addressesEndY = $this->y;

        if (!$order->getIsVirtual()) {
            $this->y = $addressesStartY;
            $shippingAddress = $shippingAddress ?? [];
            foreach ($shippingAddress as $value) {
                if ($value !== '') {
                    $text = [];
                    foreach ($this->string->split($value, 45, true, true) as $_value) {
                        $text[] = $this->rtlTextHandler->reverseRtlText($_value);
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
                        $this->y -= 15;
                    }
                }
            }

            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;

            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 275, $this->y - 25);
            $page->drawRectangle(275, $this->y, 570, $this->y - 25);

            $this->y -= 15;
            $this->_setFontBold($page, 12);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $page->drawText(__('Payment Method:'), 35, $this->y, 'UTF-8');
            $page->drawText(__('Shipping Method:'), 285, $this->y, 'UTF-8');

            $this->y -= 10;
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($page, 10);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments = $this->y - 15;
        } else {
            $yPayments = $addressesStartY;
            $paymentLeft = 285;
        }

        foreach ($payment as $value) {
            if (trim($value) != '') {
                //Printing "Payment Method" lines
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $page->drawText(strip_tags(trim($_value)), $paymentLeft, $yPayments, 'UTF-8');
                    $yPayments -= 15;
                }
            }
        }

        if ($order->getIsVirtual()) {
            // replacement of Shipments-Payments rectangle block
            $yPayments = min($addressesEndY, $yPayments);
            $page->drawLine(25, $top - 25, 25, $yPayments);
            $page->drawLine(570, $top - 25, 570, $yPayments);
            $page->drawLine(25, $yPayments, 570, $yPayments);

            $this->y = $yPayments - 15;
        } else {
            $topMargin = 15;
            $methodStartY = $this->y;
            $this->y -= 15;

            if (isset($shippingMethod) && \is_string($shippingMethod)) {
                foreach ($this->string->split($shippingMethod, 45, true, true) as $_value) {
                    $page->drawText(strip_tags(trim($_value)), 285, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }

            $yShipments = $this->y;
            $totalShippingChargesText = "("
                . __('Total Shipping Charges')
                . " "
                . $order->formatPriceTxt($order->getShippingAmount())
                . ")";

            $page->drawText($totalShippingChargesText, 285, $yShipments - $topMargin, 'UTF-8');
            $yShipments -= $topMargin + 10;

            $tracks = [];
            if ($shipment) {
                $tracks = $shipment->getAllTracks();
            }
            if (count($tracks)) {
                $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->setLineWidth(0.5);
                $page->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                $page->drawLine(400, $yShipments, 400, $yShipments - 10);
                //$page->drawLine(510, $yShipments, 510, $yShipments - 10);

                $this->_setFontRegular($page, 9);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                //$page->drawText(__('Carrier'), 290, $yShipments - 7 , 'UTF-8');
                $page->drawText(__('Title'), 290, $yShipments - 7, 'UTF-8');
                $page->drawText(__('Number'), 410, $yShipments - 7, 'UTF-8');

                $yShipments -= 20;
                $this->_setFontRegular($page, 8);
                foreach ($tracks as $track) {
                    $maxTitleLen = 45;
                    $endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                    $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                    $page->drawText($truncatedTitle, 292, $yShipments, 'UTF-8');
                    $page->drawText($track->getNumber(), 410, $yShipments, 'UTF-8');
                    $yShipments -= $topMargin - 5;
                }
            } else {
                $yShipments -= $topMargin - 5;
            }

            $currentY = min($yPayments, $yShipments);

            // replacement of Shipments-Payments rectangle block
            $page->drawLine(25, $methodStartY, 25, $currentY);
            //left
            $page->drawLine(25, $currentY, 570, $currentY);
            //bottom
            $page->drawLine(570, $currentY, 570, $methodStartY);
            //right

            $this->y = $currentY;
            $this->y -= 15;
        }
    }

    /**
     * Insert tax totals to pdf page
     *
     * @param \Zend_Pdf_Page $page page
     * @param mixed $source source
     * @return \Zend_Pdf_Page
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Pdf_Exception
     */
    protected function insertTaxTotals($page, $source)
    {
        $order = $source->getOrder();

        $subtotals = [];
        $discounts = [];
        $allItems = $order->getAllItems();
//        //item row amount calculation
//        /** @var Order\Invoice\Item $item */
        foreach ($allItems as $item) {
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

        $lineBlockTaxTotal = ['lines' => [], 'height' => 15];
        $lineBlockTaxAmount = ['lines' => [], 'height' => 15];
        foreach ($subtotals as $rate => $total) {
            $lineBlockTaxTotal['lines'][] = [
                [
                    'text' => __('Subtotal for tax rate %1:', $rate),
                    'feed' => 475,
                    'align' => 'right',
                    'font_size' => 10,
                    'font' => 'bold',
                ],
                [
                    'text' => $order->formatPriceTxt($total['total']),
                    'feed' => 565,
                    'align' => 'right',
                    'font_size' => 10,
                    'font' => 'bold'
                ],
            ];
            $lineBlockTaxAmount['lines'][] = [
                [
                    'text' => __('Tax amount(%1):', $rate),
                    'feed' => 475,
                    'align' => 'right',
                    'font_size' => 10,
                    'font' => 'bold'
                ],
                [
                    'text' => $order->formatPriceTxt($total['tax_amount']),
                    'feed' => 565,
                    'align' => 'right',
                    'font_size' => 10,
                    'font' => 'bold'
                ],
            ];
        }

        $page = $this->drawLineBlocks($page, [$lineBlockTaxTotal, $lineBlockTaxAmount]);
        $page->drawText(__('* is reducted tax rate item.'), 495, $this->y, 'UTF-8');

        return $page;
    }
}
