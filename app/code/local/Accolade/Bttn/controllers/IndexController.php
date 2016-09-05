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
		$customerId = Mage::helper('accolade_bttn')->getCustomerIdFromBid($bttnId);
		
		//# get order, shipping and billing methods from customer's Bt.tn account settings
		$orderMethod = Mage::helper('accolade_bttn')->getOrderMethod($customerId);
		$shippingMethod = Mage::helper('accolade_bttn')->getShippingMethod($customerId);
		$paymentMethod = Mage::helper('accolade_bttn')->getPaymentMethod($customerId);
		
		//# load customer and init session
		$customer = Mage::getModel('customer/customer')->load($customerId);
		Mage::getSingleton('customer/session')->loginById($customer->getId());
		
		//# get customer's quote
		$quote = Mage::getSingleton('checkout/session')->getQuote();

		//# get order products from wishlist
		if ($orderMethod == 'wishlist'){
			$wishList = Mage::getSingleton('wishlist/wishlist')->loadByCustomer($customerId);
			$wishListItemCollection = $wishList->getItemCollection();

			//# check if customer has items in wishlist
			if (count($wishListItemCollection)) {
				foreach ($wishListItemCollection as $item) {
					//# check if product is child product or simple product
					$childProduct = $item->getOptionByCode('simple_product');
					if ($childProduct != NULL) {
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
			//# get order products from selected order
		    $order = Mage::getModel('sales/order')->loadByIncrementId($orderMethod);
		    $items = $order->getAllVisibleItems();
		    foreach ($items as $item) {
				$productId = $item->getData('product_id');
				$productQty = $item->getData('qty_ordered');
				$product = Mage::getModel('catalog/product')->load($productId);	
				$quote->addProduct($product, $productQty);
		    }
		}

		//# get customer's default shipping address
		$shippingAddressId = Mage::getModel('customer/customer')->load($customerId)->getDefaultShipping();
		$shippingAddress = Mage::getModel('customer/address')->load($shippingAddressId);
		$shippingAddressData = array(
				'firstname' => $shippingAddress->getFirstname(),
				'lastname' => $shippingAddress->getLastname(),
				'street' => array($shippingAddress->getStreet()[0],
								  $shippingAddress->getStreet()[1]),
				'city' => $shippingAddress->getCity(),
				'region' => $shippingAddress->getRegion(),
				'postcode' => $shippingAddress->getPostcode(),
				'telephone' => $shippingAddress->getTelephone(),
				'country_id' => $shippingAddress->getCountry(),
				'region_id' => $shippingAddress->getRegionId(),
		);
		//# add shipping address to quote
		$shippingAddress = $quote->getShippingAddress()->addData($shippingAddressData);
		
		$shippingAddress->setCollectShippingRates(true)
			->collectShippingRates()
			->setShippingMethod($shippingMethod)
			->setPaymentMethod($paymentMethod);
		
		$quote->getPayment()->importData(array('method' => $paymentMethod));
		$quote->setStoreId($customer->getStoreId());
		$quote->collectTotals()->save();

		//# init checkout
		$checkout = Mage::getSingleton('checkout/type_onepage');
		$checkout->initCheckout();
		
		//# try to save order
		try {
			$checkout->saveOrder();
		}
		catch (Exception $ex) {
			echo $ex->getMessage();
			//# send message to API - error
		}			
    }
}