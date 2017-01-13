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
        Mage::log('Button press received', null, 'bttn-press.log', true);
        Mage::log('API Key: ' . $this->getRequest()->getHeader('X-Api-Key'), null, 'bttn-press.log', true);
        Mage::log("TRUE: " . true, null, 'bttn-press.log', true);
        Mage::log("FALSE: " . false, null, 'bttn-press.log', true);
        $keyValid = Mage::helper('accolade_bttn/api')
            ->checkKey(
                'press', 
                $this->getRequest()->getHeader('X-Api-Key')
            );
        Mage::log(
            $keyValid, 
            null, 
            'bttn-press.log', 
            true
        );
        if (Mage::helper('accolade_bttn/api')->checkKey(
            'press', 
            $this->getRequest()->getHeader('X-Api-Key')
        )
        ) {
            Mage::log('Checking data...', null, 'bttn-press.log', true);
            $data = json_decode(file_get_contents('php://input'));
            if (is_object($data)) {
                Mage::log('Data is in the right format', null, 'bttn-press.log', true);
                // Make sure all necessary data is in place
                if (isset($data->association) 
                    && isset($data->button) 
                    && isset($data->type)
                ) {
                    Mage::log('Data is complete', null, 'bttn-press.log', true);
                    $bttnId = $data->button;
                    Mage::log('Button: ' . $bttnId, null, 'bttn-press.log', true);
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
                        Mage::log('Purchase complete', null, 'bttn-press.log', true);
                    }
                    catch (Exception $ex) {
                        $error = $ex->getMessage();
                        Mage::log($error, null, 'bttn-error.log');
                        // send message to API - error
                    }
                } else {
                    Mage::log('Incomplete data', null, 'bttn-press.log', true);
                }
            } else {
                Mage::log('Incorrect data type', null, 'bttn-press.log', true);
            }
        } else {
            Mage::log('Invalid API key', null, 'bttn-press.log', true);
            return false;
        }
    }
}
