<?php

class Accolade_Bttn_Model_Key extends Mage_Core_Model_Abstract {

    private $_keyScopes = [
        'associate',
        'read',
        'press',
        'write'
    ];

    public function _construct()
    {
        parent::_construct();
        $this->_init('accolade_bttn/key', 'id');
    }

    public function getModelFromKey($key)
    {
        $keyCollection = $this->getCollection()
            ->addFieldToFilter('key', $key);
        if ($keyCollection->getSize()) {
            return $bttns;
        }
        return false;
    }

    /*
     * Check all keys to make sure they exist for each scope and
     * are not expired. Run in cron once per month
     * 
     */
    public function checkAllKeys()
    {
        $keys = $this->getCollection()
            ->AddAttributeToSelect('*');
        if ($keys->getSize() == 0) {
            foreach ($this->keyScopes as $scope) {
                Mage::helper('accolade_bttn/api')->newKey($scope);
            }
        }
    }
}
