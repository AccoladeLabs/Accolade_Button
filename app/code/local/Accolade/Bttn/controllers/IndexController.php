<?php
class Accolade_Bttn_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function preDispatch()
    {
        Mage::app()->getRequest()->setParam('forwarded', true);
        return parent::preDispatch();
    }

    public function indexAction()
    {
    }
}
