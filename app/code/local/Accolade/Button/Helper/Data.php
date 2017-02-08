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
 * The helper class for the generic functions. 
 *
 * @category Magento
 * @package  Accolade_Button
 * @author   Accolade Partners <info@accolade.fi>
 * @license  OSL-3.0 https://opensource.org/licenses/OSL-3.0 
 * @link     https://accolade.fi
 */

class Accolade_Button_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Get config to determine whether or not to show the taxes
     *
     * @return bool true if show tax or false if not
     */
    public function showTax()
    {
        return Mage::getSingleton('tax/config')
            ->displayCartPricesInclTax(Mage::app()->getStore());
    }
}
