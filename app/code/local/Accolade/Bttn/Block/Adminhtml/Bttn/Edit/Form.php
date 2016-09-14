<?php

/**
 * Created by PhpStorm.
 * User: Billy
 * Date: 9/14/2016
 * Time: 12:15 PM
 */
class Accolade_Bttn_Block_Adminhtml_Bttn_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method' => 'post'
            )
        );
        $this->setForm($form);
        $fieldset = $form->addFieldset('bttn_form', array('legend' => Mage::helper('accolade_bttn')->__('Bt.tn Association')));

        $customers = Mage::helper('accolade_bttn')->getCustomerDropdownValues();

        $fieldset->addField('button_id', 'text', array(
            'label' => Mage::helper('accolade_bttn')->__('Button ID'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'button_id'
        ));

        $fieldset->addField('customer_id', 'select', array(
            'label' => Mage::helper('accolade_bttn')->__('Customer'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'customer_id',
            'values' => $customers,
        ));

        $fieldset->addField('shipping_method', 'select', array(
            'label' => Mage::helper('accolade_bttn')->__('Shipping Method'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'shipping_method',
            'values' => array()
        ));

        $fieldset->addField('payment_method', 'select', array(
            'label' => Mage::helper('accolade_bttn')->__('Payment Method'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'payment_method',
            'values' => Mage::getModel('accolade_bttn/options')->toOptionArray()
        ));

        $fieldset->addField('order_method', 'select', array(
            'label' => Mage::helper('accolade_bttn')->__('Order Method'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'order_method',
            'values' => array()
        ));

        if ( Mage::getSingleton('adminhtml/session')->getBttnData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getBttnData());
            Mage::getSingleton('adminhtml/session')->setBttnData(null);
        } elseif ( Mage::registry('bttn_data') ) {
            $form->setValues(Mage::registry('bttn_data')->getData());
        }

        $form->setUseContainer(true);
        return parent::_prepareForm();
    }
}