<?php

$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('accolade_bttn_associations')};
DROP TABLE IF EXISTS {$this->getTable('accolade_bttn_keys')};

CREATE TABLE {$this->getTable('accolade_bttn_associations')} (
    `entity_id` int(11) unsigned NOT NULL AUTO_INCREMENT,  
    `customer_id` int(16) unsigned NOT NULL,  
    `button_id` varchar(19) NOT NULL,
    `association_id` varchar(100) NOT NULL,
    `shipping_method` varchar(40) NOT NULL,  
    `payment_method` varchar(40) NOT NULL,
	`order_method` varchar(40) NOT NULL, 
    PRIMARY KEY (`entity_id`)
   ) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('accolade_bttn_keys')} (
    `id` int(8) unsigned NOT NULL AUTO_INCREMENT,  
    `active` int(2) unsigned NOT NULL,  
    `name` varchar(256) NOT NULL,
    `key` varchar(128) NOT NULL,
    `prefix` varchar(64) NOT NULL,
    `scope` varchar(64) NOT NULL,
    `created` datetime NOT NULL,
    `expires` datetime NOT NULL,
    PRIMARY KEY (`id`)
   ) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
");

$installer->endSetup();
