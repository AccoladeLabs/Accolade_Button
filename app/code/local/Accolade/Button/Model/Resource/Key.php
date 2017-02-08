<?php

class Accolade_Button_Model_Resource_Key extends Mage_Core_Model_Resource_Db_Abstract {
	
	public function _construct(){
		$this->_init('accolade_button/key', 'entity_id');
	}
	
}
