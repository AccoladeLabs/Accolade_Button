<?php
 
class Accolade_Bttn_Helper_Data extends Mage_Core_Helper_Abstract {
	public function getCustomerDropdownValues()
    {
        $customers = Mage::getModel('customer/customer')
            ->getCollection()
            ->addAttributeToSort('email', 'ASC')
            ->getData();
        $collection = array(
            '' => '— ' . $this->__('Please Select a Customer') . ' —'
        );
        foreach ($customers as $customer) {
            $collection[$customer['entity_id']] = $customer['email'];
        }
        return $collection;
    }
}