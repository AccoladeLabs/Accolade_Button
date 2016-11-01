<?php

$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('accolade_bttn')};

CREATE TABLE {$this->getTable('accolade_bttn')} (
    `entity_id` int(11) unsigned NOT NULL AUTO_INCREMENT,  
    `customer_id` int(16) unsigned NOT NULL,  
    `button_id` varchar(19) NOT NULL,
    `association_id` varchar(100) NOT NULL,
    `shipping_method` varchar(40) NOT NULL,  
    `payment_method` varchar(40) NOT NULL,
	`order_method` varchar(40) NOT NULL, 
    PRIMARY KEY (`entity_id`)
   ) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
");

$installer->endSetup();