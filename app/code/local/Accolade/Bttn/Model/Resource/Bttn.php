<?php

class Accolade_Bttn_Model_Resource_Bttn extends Mage_Core_Model_Resource_Db_Abstract {
	
	public function _construct(){
		$this->_init('accolade_bttn/bttn', 'entity_id');
	}
	
}