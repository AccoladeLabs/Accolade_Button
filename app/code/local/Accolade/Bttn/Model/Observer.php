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
 * The observer class simply watches for the settings to be updated in order
 * to check that all the API keys are in order
 *
 * @category Magento
 * @package  Accolade_Bttn
 * @author   Accolade Partners <info@accolade.fi>
 * @license  OSL-3.0 https://opensource.org/licenses/OSL-3.0 
 * @link     https://accolade.fi
 */
class Accolade_Bttn_Model_Observer
{
    /**
     * Check that all API keys exist and are up to date
     *
     * @return null
     */
    public function checkAllApiKeys()
    {
        Mage::log("The Accolade Bttn settings were updated", null, 'bttn-observer.log', true);
        if (Mage::helper('accolade_bttn/api')->getApiKey('admin') != '') {
            Mage::getModel('accolade_bttn/key')->checkAllKeys();
        }
    }
}

