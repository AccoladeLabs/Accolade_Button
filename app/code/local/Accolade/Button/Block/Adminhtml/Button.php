<?php

/**
 * Created by PhpStorm.
 * User: Billy
 * Date: 9/14/2016
 * Time: 9:46 AM
 */
class Accolade_Button_Block_Adminhtml_Button extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'accolade_button';
        $this->_controller = 'adminhtml_button';
        $this->_headerText = Mage::helper('accolade_button')->__('Manage Associations');

        parent::__construct();
    }
}