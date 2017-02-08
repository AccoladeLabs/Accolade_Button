<?php

/**
 * Created by PhpStorm.
 * User: Billy
 * Date: 9/14/2016
 * Time: 12:15 PM
 */
class Accolade_Button_Block_Adminhtml_Button_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
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
        $fieldset = $form->addFieldset('button_form', array('legend' => Mage::helper('accolade_button')->__('Button Association')));

        $customers = Mage::helper('accolade_button')->getCustomerDropdownValues();

        $fieldset->addField('button_id', 'text', array(
            'label' => Mage::helper('accolade_button')->__('Button ID'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'button_id'
        ));

        $fieldset->addField('customer_id', 'select', array(
            'label' => Mage::helper('accolade_button')->__('Customer'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'customer_id',
            'values' => $customers,
        ));

        $fieldset->addField('shipping_method', 'select', array(
            'label' => Mage::helper('accolade_button')->__('Shipping Method'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'shipping_method',
            'values' => array()
        ));

        $fieldset->addField('payment_method', 'select', array(
            'label' => Mage::helper('accolade_button')->__('Payment Method'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'payment_method',
            'values' => Mage::getModel('accolade_button/options')->toOptionArray()
        ));

        $fieldset->addField('order_method', 'select', array(
            'label' => Mage::helper('accolade_button')->__('Order Method'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'order_method',
            'values' => array()
        ));

        if ( Mage::getSingleton('adminhtml/session')->getButtonData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getButtonData());
            Mage::getSingleton('adminhtml/session')->setButtonData(null);
        } elseif ( Mage::registry('button_data') ) {
            $form->setValues(Mage::registry('button_data')->getData());
        }

        $form->setUseContainer(true);
        return parent::_prepareForm();
    }
}