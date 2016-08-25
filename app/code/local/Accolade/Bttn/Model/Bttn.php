<?php

class Accolade_Bttn_Model_Bttn extends Mage_Core_Model_Abstract {
	
	public function _construct()
	{
		parent::_construct();
		$this->_init('accolade_bttn/bttn', 'entity_id');
	}
	
}