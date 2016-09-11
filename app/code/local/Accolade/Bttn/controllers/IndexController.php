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
		//# identify customer by Bt.tn device ID
		$bttnId = $this->getRequest()->getParam('bid');
		$customerId = Mage::getModel('accolade_bttn/bttn')->getCustomerIdFromBttnId($bttnId);
	
		//# load customer and init session
		$customer = Mage::getModel('customer/customer')->load($customerId);
		Mage::getModel('customer/session')->loginById($customerId);

		//# get customer's current cart
		$cart = Mage::getSingleton('checkout/cart')->getQuote();
		
		//# get quote
		$quote = Mage::getSingleton('checkout/session')->getQuote();
		
		//# load quote and add products
		$quote = Mage::getSingleton('accolade_bttn/bttn')->addItemsToQuote($quote);

		//# get shipping data
		$quote = Mage::getSingleton('accolade_bttn/bttn')->getShippingData($quote);

		//# save quote
		$quote->save();

		//# init checkout
		$checkout = Mage::getSingleton('checkout/type_onepage');
		$checkout->initCheckout();
		
		try {
			//# try to save order
			$checkout->saveOrder();
			
			//# restore cart
			Mage::getSingleton('checkout/cart')->setQuote($cart)->save();
		}
		catch (Exception $ex) {
			echo $ex->getMessage();
			//# send message to API - error
		}			
    }
}