<?php

/**
 * This file is part of the Accolade Button for Commerce Magento module.
 * Please see the license in the root of the directory or at the link below.
 *
 * PHP Version 5.6
 *
 * @category Magento
 * @package  Accolade_Button
 * @author   Accolade Partners <info@accolade.fi>
 * @license  OSL-3.0 https://opensource.org/licenses/OSL-3.0 
 * @link     https://accolade.fi
 */

/**
 * The model class for the Button data. Allows for easy access to all of the
 * button settings and association data
 *
 * @category Magento
 * @package  Accolade_Button
 * @author   Accolade Partners <info@accolade.fi>
 * @license  OSL-3.0 https://opensource.org/licenses/OSL-3.0 
 * @link     https://accolade.fi
 */

class Accolade_Button_Model_Button extends Mage_Core_Model_Abstract
{

    /**
     * Initializes the model
     *
     * @return null
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('accolade_button/button', 'entity_id');
    }

    /**
     * Retrieve customer associated with button model
     *
     * @return Mage_Core_Model_Customer_Session
     */
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * Retrieve the customer's ID associated with the button
     *
     * @return int $customerID
     */
    public function getCustomerId()
    {
        return Mage::getSingleton('customer/session')->getCustomer()->getId();
    }

    /**
     * Retrieve the default shipping address for the customer
     *
     * @return Mage_Core_Model_Customer_Address
     */
    public function getDefaultShipping()
    {
        $shippingData = Mage::getModel('customer/address')
            ->load($this->getCustomer()->default_shipping)->getData();
        if ($shippingData) {
            return $shippingData;
        }
        return false;
    }

    /**
     * Retrieve the street address for the customer's default shipping address
     *
     * @return string Street address
     */
    public function getStreetAddress()
    {
        if (isset($this->getDefaultShipping()['street'])) {
            return $this->getDefaultShipping()['street'];
        }
        return false;
    }

    /**
     * Retrieve the country code for the customer's default shipping address
     *
     * @return string country code
     */
    public function getCountryCode()
    {
        if (isset($this->getDefaultShipping()['country_id'])) {
            return $this->getDefaultShipping()['country_id'];
        }
        return false;
    }

    /**
     * Retrieve the postal code for the customer's default shipping address
     *
     * @return string postal code
     */
    public function getPostcode()
    {
        if (isset($this->getDefaultShipping()['postcode'])) {
            return $this->getDefaultShipping()['postcode'];
        }
        return false;
    }

    /**
     * Confirm that the default shipping address for the customer is filled in
     *
     * @return bool true if filled in, false if empty
     */
    public function getCheckShippingAddress()
    {
        if ($this->getStreetAddress() !== null 
            && $this->getCountryCode() !== null 
            && $this->getPostcode() !== null
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retrieve the primary key identifier for the button associations in
     * the database
     *
     * @return string $entityId
     */
    public function getButtonEntityId()
    {
        $buttonEntityId = $this->getCollection()
            ->addFieldToFilter('customer_id', $this->getCustomerId())
            ->addFieldToSelect('entity_id');
        $id = $buttonEntityId->getColumnValues('entity_id');
        if (count($id)) {
            return $id[0];
        }
        return false;
    }

    /**
     * Retrieve all customer button associations
     *
     * @return Accolade_Button_Model_Button_Collection
     */
    public function getCustomerButtons()
    {
        $buttons = $this->getCollection()
            ->addFieldToFilter('customer_id', $this->getCustomerId())
            ->addFieldToSelect('button_id');
        if ($buttons->getSize()) {
            return $buttons;
        }
        return false;
    }

    /**
     * Set the customer's current button for editing and deleting
     *
     * @param Mage_Core_Model_Customer $customer The customer to attach the 
     * button to
     * @param string                   $buttonId   The ID of the button to attach
     *
     * @return int error code
     */
    public function setCustomerButton($customer, $buttonId)
    {
        // Make sure we can get the button
        if ($button = $this->getButtonById($buttonId)) {
            // Make sure the button belongs to the customer trying to access it
            if ($customer) {
                $customer->setButton($button);
                Mage::log($button->getEntityId(), null, 'model.log', true);
                return 0;
            } else {
                Mage::getSingleton('core/session')
                    ->addError($this->__('Unable to retrieve session data.'));
                return 1;
            }
        } else {
            Mage::getSingleton('core/session')
                ->addError($this->__('Unable to retrieve button data.'));
            return 1;
        }
    }

    /**
     * Retrieve the button model from the button ID
     *
     * @param string $buttonId the ID of the button to retrieve
     *
     * @return mixed Accolade_Button_Model_Button on success and false on failure
     */
    public function getButtonById($buttonId)
    {
        $buttons = $this->getCollection()
            ->addFieldToFilter('customer_id', $this->getCustomerId())
            ->addFieldToFilter('button_id', $buttonId)
            ->addFieldToSelect('entity_id');
        $button = $buttons->getFirstItem();
        if ($button) {
            return $this->load($button->getEntityId());
        }
        return false;
    }

    /**
     * Retrieve the customer's ID from the button ID
     *
     * @param string $buttonId the ID of the button to retrieve
     *
     * @return mixed string on success and false on failure
     */
    public function getCustomerIdFromButtonId($buttonId)
    {
        $customerId = $this->getCollection()
            ->addFieldToFilter('button_id', $buttonId)
            ->addFieldToSelect('customer_id');
        $id = $customerId->getColumnValues('customer_id');
        if (count($id)) {
            return $id[0];
        }
        return false;
    }

    /**
     * Retrieve the shipping method frontend name from the code
     *
     * @return string $shippingTitle
     */
    public function getShippingTitleByCode()
    {
        $shippingTitle = '';
        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
        foreach ($methods as $_ccode => $_carrier) {
            $_methodOptions = array();
            if ($_methods = $_carrier->getAllowedMethods()) {
                foreach ($_methods as $_mcode => $_method) {
                    $_code = $_ccode . '_' . $_mcode;
                    $_methodOptions[] = array(
                        'value' => $_code, 
                        'label' => $_method
                    );
                }
                if (!$_title = Mage::getStoreConfig("carriers/$_ccode/title")) {
                    $_title = $_ccode;
                }
            }
        }
        return $shippingTitle;
    }

    /**
     * Retrieve the payment method frontend name from the code
     *
     * @return string $paymentTitle
     */
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

    /**
     * Confirm that order/wishlist is properly configured for purchase
     *
     * @return mixed
     */
    public function getCheckTemplate()
    {
        $isValid = 1;
        if ($this->getOrderMethod() == 'wishlist') {
            $wishList = Mage::getSingleton('wishlist/wishlist')
                ->loadByCustomer($this->getCustomerId());
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
            $order = Mage::getModel('sales/order')
                ->loadByIncrementId($this->getOrderMethod());
            $items = $order->getItemsCollection();
            foreach ($items as $item) {
                if (!$item->getProduct()->getId()) {
                    $isValid = 0;
                }
            }
        }
        return $isValid;
    }

    /**
     * Attach products to quote
     *
     * @param Mage_Core_Model_Quote $quote The quote to attach the shipping 
     * data to
     *
     * @return Mage_Core_Model_Quote
     */
    public function addItemsToQuote($quote)
    {
        if ($this->getOrderMethod() == 'wishlist') {
            //# get products from wishlist
            $wishList = Mage::getSingleton('wishlist/wishlist')
                ->loadByCustomer($this->getCustomerId());
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
            $order = Mage::getModel('sales/order')
                ->loadByIncrementId($this->getOrderMethod());
            $items = $order->getItemsCollection();
            foreach ($items as $item) {
                $productId = $item->getProduct()->getId();
                $product = Mage::getModel('catalog/product')->load($productId);
                $quote->addProduct($product, $item->getQtyOrdered());
            }
        }
        return $quote;
    }

    /**
     * Attach shipping data to quote
     *
     * @param Mage_Core_Model_Quote $quote The quote to attach the shipping 
     * data to
     *
     * @return Mage_Core_Model_Quote
     */
    public function getShippingData($quote)
    {
        //# get customer's default shipping address
        $shippingAddressId = Mage::getModel('customer/customer')
            ->load($this->getCustomerId())->getDefaultShipping();
        $shippingAddress = Mage::getModel('customer/address')
            ->load($shippingAddressId);
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
        $shippingAddress = $quote->getShippingAddress()
            ->addData($shippingAddressData);
        //# collect rates
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod($this->getShippingMethod())
            ->setPaymentMethod($this->getPaymentMethod());
        if ($this->getPaymentMethod()) {
            // Handle errors properly
            try {
                $quote->getPayment()
                    ->importData(array('method' => $this->getPaymentMethod()));
            } catch (Exception $e) {
                Mage::getSingleton('customer/session')->addError(
                    Mage::helper('core')->__(
                        'The selected payment method is not available. ' .
                        'Please select another before choosing the shipping method'
                    )
                );
                return $quote;
            }
        }
        $quote->setStoreId(
            Mage::getSingleton('customer/customer')
            ->load($this->getCustomerId())->getStoreId()
        );
        $quote->collectTotals();
        return $quote;
    }

    /**
     * Retrieve the totals in various formats for the order
     *
     * @param Mage_Sales_Model_Quote $quote    The quote to get the totals for
     * @param string                 $variable If set, get shipping rates
     * instead
     *
     * @return mixed
     */
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
        if (!Mage::helper('accolade_button')->showTax()) {
            $address->setGrandTotal(
                $address->getGrandTotal() - $address->getData('tax_amount')
            );
        }
        //# get shipping rates
        if ($variable == 'shipping') {
            $rates = $address->collectShippingRates()->getGroupedAllShippingRates();
            return $rates;
        } else {
            return $quote;
        }
    }

    /**
     * Retrieve store URL to post edit requests for button association data
     *
     * @return string $editUrl
     */
    public function getEditUrl() 
    {
        return Mage::getUrl("*/*/edit/id/" . $this->getButtonId());
    }

    /**
     * Retrieve store URL to post delete requests for button association data
     *
     * @return string $deleteUrl
     */
    public function getDeleteUrl() 
    {
        return Mage::getUrl("*/*/delete/id/" . $this->getButtonId());
    }
}
