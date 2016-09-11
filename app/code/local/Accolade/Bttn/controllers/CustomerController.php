<?php
class Accolade_Bttn_CustomerController extends Mage_Core_Controller_Front_Action
{
	public function preDispatch()
	{
		parent::preDispatch();
        $action = $this->getRequest()->getActionName();
		$loginUrl = Mage::helper('customer')->getLoginUrl();
		if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
			$this->setFlag('', self::FLAG_NO_DISPATCH, true);
		}
	}

    public function indexAction()
    {
    	$this->loadLayout();
		$this->renderLayout();
    }
	
	public function createAction()
	{
		$checkShippingAddress = Mage::getModel('accolade_bttn/bttn')->getCheckShippingAddress();
		if ($_POST['button_id'] && $_POST['payment_method'] && $_POST['order_method']) {
			if (strlen($_POST['button_id']) == 16) {
				$customerId = Mage::app()->getRequest()->getParam('id');
				$data       = array('customer_id' => Mage::getModel('accolade_bttn/bttn')->getCustomerId(),
								'button_id' => $_POST['button_id'],
								'payment_method' => $_POST['payment_method'],
								'order_method' => $_POST['order_method']
							   );
				$model = Mage::getSingleton('accolade_bttn/bttn')->setData($data);
				
				try {
					$model->save();
				} catch (Exception $e){
					Mage::getSingleton('core/session')->addError('Error');
				}
			}
			else if (!$checkShippingAddress) {
				Mage::getSingleton('core/session')->addError('Please add your address from the account settings!');
			}
			else {
				Mage::getSingleton('core/session')->addError($this->__('Bt.tn device ID invalid!')); 
			}
		} else {
			Mage::getSingleton('core/session')->addError('All required fields must be filled!');
		}
		return $this->_redirectReferer();
	}
	
    public function saveAction()
    {
		$entityId = Mage::getSingleton('accolade_bttn/bttn')->getBttnEntityId();
		
		$error = 0;
		if ($_POST['button_id']) {
			$button_id = $_POST['button_id'];
			if (strlen($button_id) == 16) {
				$data = array('button_id' => $button_id);
				$model = Mage::getModel('accolade_bttn/bttn')->load($entityId)->addData($data);
				
				try {
					$model->setId($entityId)->save();
				} catch (Exception $e){
					$error = 1;
					Mage::log($e->getMessage()); 
				}
			} else {
				$error = 1;
				Mage::getSingleton('core/session')->addError($this->__('Bt.tn device ID invalid!')); 
			}
		}
		
		if ($_POST['shipping_method']) {
			$shipping_method = $_POST['shipping_method'];
			$data = array('shipping_method' => $shipping_method);
			$model = Mage::getSingleton('accolade_bttn/bttn')->load($entityId)->addData($data);
			
			try {
				$model->setId($entityId)->save();
			} catch (Exception $e){
				$error = 1;
				Mage::log($e->getMessage()); 
			}
		}
		
		if ($_POST['payment_method']) {
			$payment_method = $_POST['payment_method'];
			$data = array('payment_method' => $payment_method);
			$model = Mage::getSingleton('accolade_bttn/bttn')->load($entityId)->addData($data);
			
			try {
				$model->setId($entityId)->save();
			} catch (Exception $e){
				$error = 1;
				Mage::log($e->getMessage()); 
			}
		}
		
		if ($_POST['order_method']) {
			$order_method = $_POST['order_method'];
			$data = array('order_method' => $order_method);
			$model = Mage::getSingleton('accolade_bttn/bttn')->load($entityId)->addData($data);
			
			try {
				$model->setId($entityId)->save();
			} catch (Exception $e){
				$error = 1;
				Mage::log($e->getMessage()); 
			}
		}
		
		if ($error == 0) {
			Mage::getSingleton('core/session')->addSuccess('Bt.tn settings successfully updated!');
		}
		return $this->_redirectReferer();
	}
}