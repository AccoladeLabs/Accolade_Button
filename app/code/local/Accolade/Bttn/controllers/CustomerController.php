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
		if($_POST['button_id'] && $_POST['shipping_method'] && $_POST['payment_method'])
		{
			$customerId = Mage::app()->getRequest()->getParam('id');
			$data = array('customer_id'=>$customerId,'button_id'=>$_POST['button_id'],'shipping_method'=>$_POST['shipping_method'],'payment_method'=>$_POST['payment_method']);
			$model = Mage::getModel('accolade_bttn/bttn')->setData($data);
			try {
				$insertId = $model->save()->getId();
				echo "Data successfully inserted. Insert ID: ".$insertId;
			} catch (Exception $e){
				Mage::log($e->getMessage());   
			}
		}
		return $this->_redirectReferer();
	}
	
    public function saveAction()
    {
		$entity_id = Mage::app()->getRequest()->getParam('id');
		if($_POST['button_id'])
		{
			$button_id = $_POST['button_id'];
			$data = array('button_id' => $button_id);
			$model = Mage::getModel('accolade_bttn/bttn')->load($entity_id)->addData($data);
			
			try {
				$model->setId($entity_id)->save();
			} catch (Exception $e){
				Mage::log($e->getMessage()); 
			}
		}
		if($_POST['shipping_method']){
			$shipping_method = $_POST['shipping_method'];
			$data = array('shipping_method' => $shipping_method);
			$model = Mage::getModel('accolade_bttn/bttn')->load($entity_id)->addData($data);
			
			try {
				$model->setId($entity_id)->save();
			} catch (Exception $e){
				Mage::log($e->getMessage()); 
			}
		}
		if($_POST['payment_method']){
			$payment_method = $_POST['payment_method'];
			$data = array('payment_method' => $payment_method);
			$model = Mage::getModel('accolade_bttn/bttn')->load($entity_id)->addData($data);
			
			try {
				$model->setId($entity_id)->save();
			} catch (Exception $e){
				Mage::log($e->getMessage()); 
			}
		}
		return $this->_redirectReferer();
	}
}