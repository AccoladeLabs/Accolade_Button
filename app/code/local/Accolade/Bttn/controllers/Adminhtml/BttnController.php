<?php

/**
 * Created by PhpStorm.
 * User: Billy
 * Date: 9/14/2016
 * Time: 8:32 AM
 */
class Accolade_Bttn_Adminhtml_BttnController extends Mage_Adminhtml_Controller_Action
{
    public function deleteAction() {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $model = Mage::getModel('accolade_bttn/bttn');

                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('accolade_bttn')->__('The association was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function editAction() {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('accolade_bttn/bttn')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('bttn_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('accolade_bttn/bttn');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Bt.tn Association Manager'), Mage::helper('adminhtml')->__('Bt.tn Association Manager'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('accolade_bttn/adminhtml_bttn_edit'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('accolade_bttn')->__('The requested association does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function exportBttnCsvAction()
    {
        $fileName = 'accolade_bttn.csv';
        $grid = $this->getLayout()->createBlock('accolade_bttn/adminhtml_bttn_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    public function exportBttnExcelAction()
    {
        $fileName = 'accolade_bttn.xml';
        $grid = $this->getLayout()->createBlock('accolade_bttn/adminhtml_bttn_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('accolade_bttn/adminhtml_bttn_grid')->toHtml()
        );
    }

    public function indexAction() {
        $this->loadLayout();
        $this->_title($this->__("Manage Associations"));
        $this->_setActiveMenu("accolade_bttn/bttn");
        $this->_addContent($this->getLayout()->createBlock('accolade_bttn/adminhtml_bttn'));
        $this->renderLayout();
    }

    public function massDeleteAction() {
        $associationIds = $this->getRequest()->getParam('accolade_bttn');
        if(!is_array($associationIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Association(s)'));
        } else {
            try {
                foreach ($associationIds as $id) {
                    $association = Mage::getModel('accolade_bttn/bttn')->load($id);
                    $association->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        '%d association(s) were successfully deleted', count($associationIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function newAction()
    {
        $this->_forward("edit");
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {

            $model = Mage::getModel('accolade_bttn/bttn');
            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));

            try {
                if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())
                        ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }

                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('accolade_bttn')->__('The association was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('accolade_bttn')->__('Unable to save the association'));
        $this->_redirect('*/*/');
    }
}