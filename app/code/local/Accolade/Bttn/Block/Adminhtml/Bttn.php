<?php

/**
 * Created by PhpStorm.
 * User: Billy
 * Date: 9/14/2016
 * Time: 9:46 AM
 */
class Accolade_Bttn_Block_Adminhtml_Bttn extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'accolade_bttn';
        $this->_controller = 'adminhtml_bttn';
        $this->_headerText = Mage::helper('accolade_bttn')->__('Manage Associations');

        parent::__construct();
    }
}