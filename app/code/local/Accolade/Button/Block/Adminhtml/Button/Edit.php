<?php

/**
 * Created by PhpStorm.
 * User: Billy
 * Date: 9/14/2016
 * Time: 12:10 PM
 */
class Accolade_Button_Block_Adminhtml_Button_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'accolade_button';
        $this->_controller = 'adminhtml_button';

        $this->_updateButton('save', 'label', Mage::helper('accolade_button')->__('Save Association'));
        $this->_updateButton('delete', 'label', Mage::helper('accolade_button')->__('Delete Association'));

        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('button_data') && Mage::registry('button_data')->getId() ) {
            return Mage::helper('accolade_button')->__("Edit Association");
        } else {
            return Mage::helper('accolade_button')->__('Add Association');
        }
    }
}