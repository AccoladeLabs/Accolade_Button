<?php

class Accolade_Bttn_Model_Bttn extends Mage_Core_Model_Abstract {
	
	public function _construct()
	{
		parent::_construct();
		$this->_init('accolade_bttn/bttn', 'entity_id');
	}
	
	public function getCustomer()
	{
		return Mage::getSingleton('customer/session')->getCustomer();
	}
	
	public function getCustomerId()
	{
		return Mage::getSingleton('customer/session')->getCustomer()->getId();
	}
	
	public function getDefaultShipping()
	{
		return Mage::getModel('customer/address')->load($this->getCustomer()->default_shipping)->getData();
	}
	
	public function getStreetAddress()
	{
		return $this->getDefaultShipping()['street'];
	}
	
	public function getCountryCode()
	{
		return $this->getDefaultShipping()['country_id'];
	}
	
	public function getPostcode()
	{
		return $this->getDefaultShipping()['postcode'];
	}
	
	public function getCheckShippingAddress()
	{
		if ($this->getStreetAddress() !== NULL && $this->getCountryCode() !== NULL && $this->getPostcode() !== NULL) {
			return true;
		}
	}
	
	public function getBttnEntityId()
	{
		$bttnEntityId = Mage::getModel('accolade_bttn/bttn')->getCollection()
			->addFieldToFilter('customer_id', $this->getCustomerId())
			->addFieldToSelect('entity_id');
		return $bttnEntityId->getColumnValues('entity_id')[0];
	}
	
	public function getBttnId()
	{
		$bttnId = Mage::getModel('accolade_bttn/bttn')->getCollection()
			->addFieldToFilter('customer_id', $this->getCustomerId())
			->addFieldToSelect('button_id');
		return $bttnId->getColumnValues('button_id')[0];
	}
	
	public function getCustomerIdFromBttnId($bttnId)
	{
		$customerId = Mage::getModel('accolade_bttn/bttn')->getCollection()
			->addFieldToFilter('button_id', $bttnId)
			->addFieldToSelect('customer_id');
		return $customerId->getColumnValues('customer_id')[0];
	}
	
	public function getShippingMethod()
	{
		$shippingMethod = Mage::getModel('accolade_bttn/bttn')->getCollection()
			->addFieldToFilter('customer_id', $this->getCustomerId())
			->addFieldToSelect('shipping_method');
		return $shippingMethod->getColumnValues('shipping_method')[0];
	}
	
	public function getPaymentMethod()
	{
		$paymentMethod = Mage::getModel('accolade_bttn/bttn')->getCollection()
			->addFieldToFilter('customer_id', $this->getCustomerId())
			->addFieldToSelect('payment_method');
		return $paymentMethod->getColumnValues('payment_method')[0];
	}
	
	public function getOrderMethod()
	{
		$orderMethod = Mage::getModel('accolade_bttn/bttn')->getCollection()
			->addFieldToFilter('customer_id', $this->getCustomerId())
			->addFieldToSelect('order_method');
		return $orderMethod->getColumnValues('order_method')[0];
	}
	
	public function getShippingTitleByCode()
	{
		$shippingTitle = '';
		$methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
		foreach ($methods as $_ccode => $_carrier) {
			$_methodOptions = array();
			if ($_methods = $_carrier->getAllowedMethods()) {
				foreach ($_methods as $_mcode => $_method) {
					$_code = $_ccode . '_' . $_mcode;
					$_methodOptions[] = array('value' => $_code, 'label' => $_method);
				}
				if (!$_title = Mage::getStoreConfig("carriers/$_ccode/title")) {
					$_title = $_ccode;
				}
			}
		}
		return $shippingTitle;
	}
	
	public function getPaymentTitleByCode()
	{
		$payments = Mage::getSingleton('payment/config')->getActiveMethods();
		foreach ($payments as $paymentCode=>$paymentModel) {
			$title = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
			if ($paymentCode == $this->getPaymentMethod()) {
				$paymentTitle = $title;
			}
		}
		return $paymentTitle;
	}
	
	public function getCheckTemplate()
	{
		$isValid = 1;
		if ($this->getOrderMethod() == 'wishlist'){
			$wishList = Mage::getSingleton('wishlist/wishlist')->loadByCustomer($this->getCustomerId());
			$wishListItemCollection = $wishList->getItemCollection();
			//# check if customer has items in wishlist
			if (count($wishListItemCollection)) {
				foreach ($wishListItemCollection as $item) {
					//# check if product is child product or simple product
					$childProduct = $item->getOptionByCode('simple_product');
					if ($childProduct) {
						$productId = $childProduct->getValue();
					} else {
						$productId = $item->getProduct()->getId();
					}
					$product = Mage::getModel('catalog/product')->load($productId);				
					if ($product->isConfigurable()) {
						$isValid = 'wishlistHasConfigurable';
					}
				}
			} else {
				$isValid = 0;
			}
		} else {
			//# get products from selected order
			$order = Mage::getModel('sales/order')->loadByIncrementId($this->getOrderMethod());
			$items = $order->getItemsCollection();
			foreach ($items as $item) {
				if (!$item->getProduct()->getId()) {
					$isValid = 0;
				}
			}
		}
		return $isValid;
	}
	
	public function addItemsToQuote($quote)
	{
		if ($this->getOrderMethod() == 'wishlist'){
			//# get products from wishlist
			$wishList = Mage::getSingleton('wishlist/wishlist')->loadByCustomer($this->getCustomerId());
			$wishListItemCollection = $wishList->getItemCollection();
			//# check if customer has items in wishlist
			if (count($wishListItemCollection)) {
				foreach ($wishListItemCollection as $item) {
					//# check if product is child product or simple product
					$childProduct = $item->getOptionByCode('simple_product');
					if ($childProduct) {
						$productId = $childProduct->getValue();
					} else {
						$productId = $item->getProduct()->getId();
					}
					$product = Mage::getModel('catalog/product')->load($productId);				
					$quote->addProduct($product, $item->getQty());
				}
			} else {
				//# send message to API - no items
			}
		} else {
			//# get products from selected order
			$order = Mage::getModel('sales/order')->loadByIncrementId($this->getOrderMethod());
			$items = $order->getItemsCollection();
			foreach ($items as $item) {
				$productId = $item->getProduct()->getId();
				$product = Mage::getModel('catalog/product')->load($productId);				
				$quote->addProduct($product, $item->getQtyOrdered());
			}
		}
		return $quote;
	}
	
	public function getShippingData($quote)
	{
		//# get customer's default shipping address
		$shippingAddressId = Mage::getModel('customer/customer')->load($this->getCustomerId())->getDefaultShipping();
		$shippingAddress = Mage::getModel('customer/address')->load($shippingAddressId);
		$shippingAddressData = array(
				'firstname' => $shippingAddress->getFirstname(),
				'lastname' => $shippingAddress->getLastname(),
				'street' => $shippingAddress->getStreet()[0],
				'city' => $shippingAddress->getCity(),
				'region' => $shippingAddress->getRegion(),
				'postcode' => $shippingAddress->getPostcode(),
				'telephone' => $shippingAddress->getTelephone(),
				'country_id' => $shippingAddress->getCountry(),
				'region_id' => $shippingAddress->getRegionId(),
		);
		//# add shipping address to quote
		$shippingAddress = $quote->getShippingAddress()->addData($shippingAddressData);
		//# collect rates
		$shippingAddress->setCollectShippingRates(true)
			->collectShippingRates()
			->setShippingMethod($this->getShippingMethod())
			->setPaymentMethod($this->getPaymentMethod());
		$quote->getPayment()->importData(array('method' => $this->getPaymentMethod()));
		$quote->setStoreId(Mage::getSingleton('customer/customer')->load($this->getCustomerId())->getStoreId());
		$quote->collectTotals();
		return $quote;
	}
	
	public function getTotals($quote, $variable)
	{
		//# add items to quote
		$quote = $this->addItemsToQuote($quote);
		//# add shipping data to quote
		$quote = $this->getShippingData($quote);
		$address = $quote->getShippingAddress();
		$address->setCountryId($this->getCountryCode())
				->setPostcode($this->getPostcode())
				->setCollectShippingrates(true); 
		//# get shipping rates
		if ($variable == 'shipping') {
			$rates = $address->collectShippingRates()->getGroupedAllShippingRates();
			return $rates;
		} else {
			return $quote;
		}
	}
	
}