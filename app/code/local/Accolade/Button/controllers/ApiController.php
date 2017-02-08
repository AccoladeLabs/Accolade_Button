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
 * The endpoint to handle button press requests
 *
 * @category Magento
 * @package  Accolade_Button
 * @author   Accolade Partners <info@accolade.fi>
 * @license  OSL-3.0 https://opensource.org/licenses/OSL-3.0 
 * @link     https://accolade.fi
 */
class Accolade_Button_ApiController extends Mage_Core_Controller_Front_Action
{
    /**
     * Handle the button press request
     *
     * @return null
     */
    public function callbackAction()
    {
        Mage::log('Button press received', null, 'button-press.log', true);
        Mage::log('API Key: ' . $this->getRequest()->getHeader('X-Api-Key'), null, 'button-press.log', true);
        Mage::log("TRUE: " . true, null, 'button-press.log', true);
        Mage::log("FALSE: " . false, null, 'button-press.log', true);
        $keyValid = Mage::helper('accolade_button/api')
            ->checkKey(
                'press', 
                $this->getRequest()->getHeader('X-Api-Key')
            );
        Mage::log(
            $keyValid, 
            null, 
            'button-press.log', 
            true
        );
        if (Mage::helper('accolade_button/api')->checkKey(
            'press', 
            $this->getRequest()->getHeader('X-Api-Key')
        )
        ) {
            Mage::log('Checking data...', null, 'button-press.log', true);
            $data = json_decode(file_get_contents('php://input'));
            if (is_object($data)) {
                Mage::log('Data is in the right format', null, 'button-press.log', true);
                // Make sure all necessary data is in place
                if (isset($data->association) 
                    && isset($data->button) 
                    && isset($data->type)
                ) {
                    Mage::log('Data is complete', null, 'button-press.log', true);
                    $buttonId = $data->button;
                    Mage::log('Button: ' . $buttonId, null, 'button-press.log', true);
                    $button = Mage::getModel('accolade_button/button')->load($buttonId, 'button_id');
                    // identify customer by Button device ID
                    $customerId = Mage::getModel('accolade_button/button')
                        ->getCustomerIdFromButtonId($buttonId);
                    Mage::getModel('customer/session')->loginById($customerId);
                    // get customer's current cart
                    $cart = Mage::getSingleton('checkout/cart')->getQuote();
                    // get quote
                    $quote = Mage::getSingleton('checkout/session')->getQuote();
                    // load quote and add products
                    $quote = $button->addItemsToQuote($quote);
                    // get shipping data
                    $quote = $button->getShippingData($quote);
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
                        Mage::log('Purchase complete', null, 'button-press.log', true);
                    }
                    catch (Exception $ex) {
                        $error = $ex->getMessage();
                        Mage::log('Error completing purchase: ' . $error, null, 'button-error.log');
                        // send message to API - error
                    }
                } else {
                    Mage::log('Incomplete data', null, 'button-press.log', true);
                }
            } else {
                Mage::log('Incorrect data type', null, 'button-press.log', true);
            }
        } else {
            Mage::log('Invalid API key', null, 'button-press.log', true);
            return false;
        }
    }
}
