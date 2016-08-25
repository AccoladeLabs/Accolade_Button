<?php
 
class Accolade_Bttn_Helper_Data extends Mage_Core_Helper_Abstract {
	
	public function getBttnInfo($customerId)
	{
		$bttnInfoCollection = Mage::getModel('accolade_bttn/bttn')->getCollection()
			->addFieldToFilter('customer_id', $customerId)
			->addFieldToSelect('button_id')
			->addFieldToSelect('shipping_method')
			->addFieldToSelect('payment_method');
			return $bttnInfoCollection->getData();
	}
	
	public function getCustomerEntity($customerId)
	{
		$customerEntityId = Mage::getModel('accolade_bttn/bttn')->getCollection()
			->addFieldToFilter('customer_id', $customerId)
			->addFieldToSelect('entity_id');
			return $customerEntityId->getColumnValues('entity_id')[0];
	}
	
	public function getShippingMethod($customerId)
	{
		$shippingMethod = Mage::getModel('accolade_bttn/bttn')->getCollection()
			->addFieldToFilter('customer_id', $customerId)
			->addFieldToSelect('shipping_method');
			return $shippingMethod->getColumnValues('shipping_method')[0];
	}
	
	public function getPaymentMethod($customerId)
	{
		$paymentMethod = Mage::getModel('accolade_bttn/bttn')->getCollection()
			->addFieldToFilter('customer_id', $customerId)
			->addFieldToSelect('payment_method');
			return $paymentMethod->getColumnValues('payment_method')[0];
	}
	
}