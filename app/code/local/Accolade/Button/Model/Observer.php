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
 * The observer class simply watches for the settings to be updated in order
 * to check that all the API keys are in order
 *
 * @category Magento
 * @package  Accolade_Button
 * @author   Accolade Partners <info@accolade.fi>
 * @license  OSL-3.0 https://opensource.org/licenses/OSL-3.0 
 * @link     https://accolade.fi
 */
class Accolade_Button_Model_Observer
{
    /**
     * Check that all API keys exist and are up to date
     *
     * @return null
     */
    public function checkAllApiKeys()
    {
        Mage::log("The Accolade Button settings were updated", null, 'button-observer.log', true);
        if (Mage::helper('accolade_button/api')->getApiKey('admin') != '') {
            Mage::getModel('accolade_button/key')->checkAllKeys();
        }
    }
}

