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
		$customerId = $this->getRequest()->getParam('customer');
		$customer = Mage::getModel('customer/customer')->load($customerId);
		$customerData = $customer->getData();
		
		$quote = Mage::getModel('sales/quote')->setStoreId($customer->getStoreId());
		
		$quote->assignCustomer($customer);

		$wishList = Mage::getSingleton('wishlist/wishlist')->loadByCustomer($customerId);
		$wishListItemCollection = $wishList->getItemCollection();

		if (count($wishListItemCollection)) {
			foreach ($wishListItemCollection as $item) {
				$productId = $item->getOptionByCode('simple_product')->getValue();
				$product = Mage::getModel('catalog/product')->load($productId);				
				$quote->addProduct($product, $item->getQty());
			}
		}

		$address = $customer->getDefaultBilling();
		$customerAddress = Mage::getModel('customer/address')->load($address);

		$addressData = array(
				'firstname' => $customer->getFirstname(),
				'lastname' => $customer->getLastname(),
				'street' => $customerAddress->getStreet()[0],
				'city' => $customerAddress->getCity(),
				'postcode' => $customerAddress->getPostcode(),
				'telephone' => $customerAddress->getTelephone(),
				'country_id' => $customerAddress->getCountry(),
				'region_id' => $customerAddress->getRegionId(),
		);

		$billingAddress = $quote->getBillingAddress()->addData($addressData);
		$shippingAddress = $quote->getShippingAddress()->addData($addressData);

		$shippingMethod = Mage::helper('accolade_bttn')->getShippingMethod($customerId);
		$paymentMethod = Mage::helper('accolade_bttn')->getPaymentMethod($customerId);
		
		$shippingAddress->setCollectShippingRates(true)->collectShippingRates()
						->setShippingMethod($shippingMethod)
						->setPaymentMethod($paymentMethod);

		$quote->getPayment()->importData(array('method' => $paymentMethod));

		$quote->collectTotals()->save();

		$service = Mage::getModel('sales/service_quote', $quote);
		$service->submitAll();
		$order = $service->getOrder();

		printf("Created order %s\n", $order->getIncrementId());
		
    }
}