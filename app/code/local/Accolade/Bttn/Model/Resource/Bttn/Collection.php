<?php

class Accolade_Bttn_Model_Resource_Bttn_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

	public function _construct() {
		parent::_construct();
		$this->_init('accolade_bttn/bttn');
	}

}