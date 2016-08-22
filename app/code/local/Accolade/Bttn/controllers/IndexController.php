<?php
class Accolade_Bttn_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function preDispatch ()
    {
        Mage::app ()->getRequest ()->setParam ( 'forwarded', true );
        return parent::preDispatch ();
    }

    public function indexAction ()
    {
		$orderId = $_GET['id'];
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
		if($order->getIncrementId()){													
			if(!$order->canInvoice()){
				Mage::log('Cannot create an invoice.');
			}
			 Mage::log($order->getAllVisibleItems());
			$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
			if (!$invoice->getTotalQty()) {
				Mage::log('Cannot create an invoice without products.');
			}
			//$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
			$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
			$invoice->register();
			$transactionSave = Mage::getModel('core/resource_transaction')
			->addObject($invoice)
			->addObject($invoice->getOrder());
			 
			$transactionSave->save();
		}
    }
}
?>