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
 * The model class for the API keys. Allows for easy access to retrieval of 
 * existing keys from the database
 *
 * @category Magento
 * @package  Accolade_Bttn
 * @author   Accolade Partners <info@accolade.fi>
 * @license  OSL-3.0 https://opensource.org/licenses/OSL-3.0 
 * @link     https://accolade.fi
 */
class Accolade_Bttn_Model_Key extends Mage_Core_Model_Abstract
{
    private $_keyScopes = [
        'associate',
        'read',
        'press',
        'write'
    ];

    /**
     * Accolade_Bttn_Model_Key constructor function
     *
     * @return null
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('accolade_bttn/key', 'id');
    }

    /**
     * Retrieve the key model from the API key
     *
     * @param string $key the API key
     *
     * @return mixed Accolade_Bttn_Model_Key class instance on success or false
     * on failure
     */
    public function getModelFromKey($key)
    {
        $keyCollection = $this->getCollection()
            ->addFieldToFilter('key', $key);
        if ($keyCollection->getSize()) {
            return $bttns;
        }
        return false;
    }

    /**
     * Check all keys to make sure they exist for each scope and
     * are not expired. Run in cron once per month
     *
     * @return null
     */
    public function checkAllKeys()
    {
        $keys = $this->getCollection()
            ->AddFieldToSelect('*');
        if ($keys->getSize() == 0) {
            foreach ($this->_keyScopes as $scope) {
                Mage::helper('accolade_bttn/api')->newKey($scope);
            }
        }
    }

    /**
     * Retrieve the API key needed for the given scope
     *
     * @param string $scope The scope requested
     *
     * @return mixed The API key requested on success or false on failure
     */
    public function getKeyByScope($scope) 
    {
        $key = $this->getCollection()
            ->addFieldToFilter('active', 1)
            ->addFieldToFilter('scope', $scope)
            ->addFieldToSelect('key');
        if ($key->getSize() > 0) {
            return $key->getFirstItem()->getKey();
        } else {
            return false;
        }
    }
}
