<?php
/**
 * This file is part of the Accolade Button for Commerce Magento module.
 * Please see the license in the root of the directory or at the link below.
 *
 * PHP Version 5.6
 *
 * @category Magento
 * @package  Accolade_Button
 * @author   Accolade Partners <info@accolade.fi>
 * @license  OSL-3.0 https://opensource.org/licenses/OSL-3.0
 * @link     https://accolade.fi
 */

/* @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$associationTable = $installer->getConnection()
    ->newTable($installer->getTable('accolade_button_associations'))
    ->addColumn(
        'entity_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        11,
        array(
            'identity' => true,
            'nullable' => false,
            'primary'  => true,
            'unsigned' => true
        ),
        'EntityId'
    )
    ->addColumn(
        'customer_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        16,
        array(
            'nullable' => false,
            'unsigned' => true
        ),
        'CustomerId'
    )
    ->addColumn(
        'button_id',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        19,
        array(
            'nullable' => false,
        ),
        'ButtonId'
    )
    ->addColumn(
        'association_id',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        100,
        array(
            'nullable' => false
        ),
        'AssociationId'
    )
    ->addColumn(
        'shipping_method',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        40,
        array(
            'nullable' => false,
            'default'  => 1
        ),
        'ShippingMethod'
    )
    ->addColumn(
        'payment_method',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        40,
        array(
            'nullable' => false
        ),
        'PaymentMethod'
    )
    ->addColumn(
        'order_method',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        40,
        array(
            'nullable' => false
        ),
        'OrderMethod'
    );

$installer->getConnection()->createTable($associationTable);

$keyTable = $installer->getConnection()
    ->newTable($installer->getTable('accolade_button_keys'))
    ->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        8,
        array(
            'identity' => true,
            'nullable' => false,
            'primary'  => true,
            'unsigned' => true
        ),
        'ID'
    )
    ->addColumn(
        'active',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        2,
        array(
            'nullable' => false,
            'unsigned' => true
        ),
        'Active'
    )
    ->addColumn(
        'name',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        256,
        array(
            'nullable' => false,
        ),
        'Name'
    )
    ->addColumn(
        'api_key',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        128,
        array(
            'nullable' => false
        ),
        'APIKey'
    )
    ->addColumn(
        'prefix',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        64,
        array(
            'nullable' => false
        ),
        'Prefix'
    )
    ->addColumn(
        'scope',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        64,
        array(
            'nullable' => false
        ),
        'Scope'
    )
    ->addColumn(
        'created',
        Varien_Db_Ddl_Table::TYPE_DATETIME,
        null,
        array(
            'nullable' => false
        ),
        'Created'
    )
    ->addColumn(
        'expires',
        Varien_Db_Ddl_Table::TYPE_DATETIME,
        null,
        array(
            'nullable' => false
        ),
        'Expires'
    );

$installer->getConnection()->createTable($keyTable);

$installer->endSetup();
