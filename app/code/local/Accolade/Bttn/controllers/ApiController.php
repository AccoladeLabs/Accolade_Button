<?php
/**
 * This file is part of the Accolade Button for Commerce Magento module.
 * Please see the license in the root of the directory or at the link below.
 *
 * PHP Version 5.6
 *
 * @category Magento
 * @package  Accolade_Bttn
 * @author   Accolade Partners <info@accolade.fi>
 * @license  OSL-3.0 https://opensource.org/licenses/OSL-3.0 
 * @link     https://accolade.fi
 */

/**
 * The endpoint to handle button press requests
 *
 * @category Magento
 * @package  Accolade_Bttn
 * @author   Accolade Partners <info@accolade.fi>
 * @license  OSL-3.0 https://opensource.org/licenses/OSL-3.0 
 * @link     https://accolade.fi
 */
class Accolade_Bttn_ApiController extends Mage_Core_Controller_Front_Action
{
    /**
     * Handle the button press request
     *
     * @return null
     */
    public function callbackAction()
    {
        if (Mage::helper('accolade_bttn/api')->checkKey(
            'press', 
            $this->getRequest()->getHeader('X-Api-Key')
        )
        ) {
            $data = json_decode(file_get_contents('php://input'));
            if (is_object($data)) {
                // Make sure all necessary data is in place
                if (isset($data->association) 
                    && isset($data->button) 
                    && isset($data->type)
                ) {
                    $bttnId = $data->button;
                    // identify customer by Bt.tn device ID
                    $customerId = Mage::getModel('accolade_bttn/bttn')
                        ->getCustomerIdFromBttnId($bttnId);
                    Mage::getModel('customer/session')->loginById($customerId);
                    // get customer's current cart
                    $cart = Mage::getSingleton('checkout/cart')->getQuote();
                    // get quote
                    $quote = Mage::getSingleton('checkout/session')->getQuote();
                    // load quote and add products
                    $quote = Mage::getSingleton('accolade_bttn/bttn')
                        ->addItemsToQuote($quote);
                    // get shipping data
                    $quote = Mage::getSingleton('accolade_bttn/bttn')
                        ->getShippingData($quote);
                    // save quote
                    $quote->save();
                    // init checkout
                    $checkout = Mage::getSingleton('checkout/type_onepage');
                    $checkout->initCheckout();
                    try {
                        // try to save order
                        $checkout->saveOrder();
                        // restore cart
                        Mage::getSingleton('checkout/cart')->setQuote($cart)->save();
                        return 'Purchase complete';
                    }
                    catch (Exception $ex) {
                        $error = $ex->getMessage();
                        Mage::log($error, null, 'bttn-error.log');
                        // send message to API - error
                    }
                } else {
                    return 'Incomplete data';
                }
            } else {
                return 'Incorrect data type';

            }
        }
    }
}
