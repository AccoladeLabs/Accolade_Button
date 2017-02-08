<?php

/**
 * Created by PhpStorm.
 * User: Billy
 * Date: 9/14/2016
 * Time: 9:50 AM
 */
class Accolade_Button_Block_Adminhtml_Button_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('accolade_button_grid');
        $this->setDefaultSort('button_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('accolade_button/button')->getCollection();
        $collection->getSelect()->join(
            array("c" => "customer_entity"),
            "customer_id = c.entity_id",
            array("email")
        );
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('accolade_button');

        $this->addColumn('entity_id', array(
            'header' => $helper->__('ID'),
            'index'  => 'entity_id'
        ));

        $this->addColumn('button_id', array(
            'header' => $helper->__('Button ID'),
            'index'  => 'button_id'
        ));

        $this->addColumn('customer_id', array(
            'header' => $helper->__('Customer ID'),
            'index'  => 'customer_id'
        ));

        $this->addColumn('customer_name', array(
            'header' => $helper->__('Customer Email'),
            'index'  => 'email'
        ));

        $this->addExportType('*/*/exportButtonCsv', $helper->__('CSV'));
        $this->addExportType('*/*/exportButtonExcel', $helper->__('Excel XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('accolade_button');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => Mage::helper('accolade_button')->__('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => Mage::helper('accolade_button')->__('Are you sure?')
        ));
        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}