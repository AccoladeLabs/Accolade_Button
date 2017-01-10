<?php
class Accolade_Bttn_ApiController extends Mage_Core_Controller_Front_Action
{
    public function callbackAction()
    {
        //Mage::log(print_r($this->getRequest()->getPost(), true), null, 'bttn-press.log', true);
        if (Mage::helper('accolade_bttn/api')->checkKey("press", $this->getRequest()->getHeader('X-Api-Key'))) {
            $data = json_decode(file_get_contents('php://input'));
        }
    }
}

