<?php

/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the H&O Commercial License
 * that is bundled with this package in the file LICENSE_HO.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.h-o.nl/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@h-o.com so we can send you a copy immediately.
 *
 * @category    ${Namespace}
 * @package     ${Namespace}_${Module}
 * @copyright   Copyright © 2013 H&O (http://www.h-o.nl/)
 * @license     H&O Commercial License (http://www.h-o.nl/license)
 * @author      Paul Hachmang – H&O <info@h-o.nl>
 */
class Inovarti_QuoteItemRule_Model_SalesRule_Rule_Condition_Address extends Mage_SalesRule_Model_Rule_Condition_Address {

    public function loadAttributeOptions() {
        $attributes = array(
            'base_subtotal' => Mage::helper('salesrule')->__('Subtotal'),
            'total_qty' => Mage::helper('salesrule')->__('Total Items Quantity'),
            'weight' => Mage::helper('salesrule')->__('Total Weight'),
            'payment_method' => Mage::helper('salesrule')->__('Payment Method'),
            'shipping_method' => Mage::helper('salesrule')->__('Shipping Method'),
            'postcode' => Mage::helper('salesrule')->__('Shipping Postcode'),
            'region' => Mage::helper('salesrule')->__('Shipping Region'),
            'region_id' => Mage::helper('salesrule')->__('Shipping State/Province'),
            'country_id' => Mage::helper('salesrule')->__('Shipping Country'),
            'cc_type' => Mage::helper('salesrule')->__('Credit Card Type'),
            'cc_parcelamento' => Mage::helper('salesrule')->__('Parcelamento'),
        );

        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getInputType() {
        switch ($this->getAttribute()) {
            case 'base_subtotal': case 'weight': case 'total_qty': case 'cc_parcelamento':
                return 'numeric';

            case 'shipping_method': case 'payment_method': case 'country_id': case 'cc_type': case 'region_id':
                return 'select';
        }
        return 'string';
    }

    public function getValueElementType() {
        switch ($this->getAttribute()) {
            case 'shipping_method': case 'payment_method': case 'country_id': case 'region_id': case 'cc_type' :
                return 'select';
        }
        return 'text';
    }

    public function getValueSelectOptions() {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'country_id':
                    $options = Mage::getModel('adminhtml/system_config_source_country')
                            ->toOptionArray();
                    break;

                case 'region_id':
                    $options = Mage::getModel('adminhtml/system_config_source_allregion')
                            ->toOptionArray();
                    break;

                case 'shipping_method':
                    $options = Mage::getModel('adminhtml/system_config_source_shipping_allmethods')
                            ->toOptionArray();
                    break;

                case 'payment_method':
                    $options = Mage::getModel('adminhtml/system_config_source_payment_allmethods')
                            ->toOptionArray();
                    break;

                case 'cc_type':
                    $options = Mage::getModel('adminhtml/system_config_source_payment_cctype')
                            ->toOptionArray();
                    break;

                default:
                    $options = array();
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    /**
     * Validate Address Rule Condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object) {
        
        $address = $object;
        if (!$address instanceof Mage_Sales_Model_Quote_Address) {
            if ($object->getQuote()->isVirtual()) {
                $address = $object->getQuote()->getBillingAddress();
            }
            else {
                $address = $object->getQuote()->getShippingAddress();
            }
        }

        if ('payment_method' == $this->getAttribute() && ! $address->hasPaymentMethod()) {
            $address->setPaymentMethod($object->getQuote()->getPayment()->getMethod());
        }
        if ('cc_type' == $this->getAttribute() && !$address->hasCcType()) {
            $address->setCcType($object->getQuote()->getPayment()->getCcType());
        }
        if ('cc_parcelamento' == $this->getAttribute() && !$address->hasCcParcelamento()) {
            $address->setCcParcelamento($object->getQuote()->getPayment()->getAdditionalInformation('cc_parcelamento'));
            Mage::log(print_r($object->getQuote()->getPayment()->getAdditionalInformation('cc_parcelamento'), 1), null, 'mundipagg_tem_setDiscount.log', true);
        }

        return parent::validate($address);
    }

}
