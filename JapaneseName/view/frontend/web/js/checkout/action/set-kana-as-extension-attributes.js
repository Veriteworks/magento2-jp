/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'Magento_Checkout/js/model/quote',
    'jquery',
    'underscore',
    'mage/utils/wrapper'
], function (quote, $, _, wrapper) {
    'use strict';

    /**
     * Copy kana attribute from custom to extensions attributes.
     *
     * @param {Object} address
     * @param {String} attr
     */
    var addKanaExtensionAttribute = function (address, attr) {
        var attrCode = address.customAttributes[attr]['attribute_code'];

        if (attrCode === 'firstnamekana' || attrCode === 'lastnamekana') {
            address.extensionAttributes[attrCode] = address.customAttributes[attr].value;
        }
    };

    /**
     * Kana fields added as custom attributes based on customer address declaration.
     * To persist kana in quote and order addresses they data should be transferred to extension attributes.
     */
    return function (addressData) {
        return wrapper.wrap(addressData, function (originalAction) {
            var processedAddress = originalAction();
            var attr;

            if (typeof processedAddress.extensionAttributes === 'undefined') {
                processedAddress.extensionAttributes = {};
            }

            for (attr in  processedAddress.customAttributes) {
                if (processedAddress.customAttributes.hasOwnProperty(attr)) {
                    addKanaExtensionAttribute(processedAddress, attr);
                }
            }

            return processedAddress;
        });
    };
});
