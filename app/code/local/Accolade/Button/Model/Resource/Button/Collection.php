<?php

class Accolade_Button_Model_Resource_Button_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

	public function _construct() {
		parent::_construct();
		$this->_init('accolade_button/button');
	}

}