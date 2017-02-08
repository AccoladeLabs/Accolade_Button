<?php

/**
 * Created by PhpStorm.
 * User: Billy
 * Date: 9/14/2016
 * Time: 8:32 AM
 */
class Accolade_Button_Adminhtml_ButtonController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('system/config');
    }

    public function deleteAction() {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $model = Mage::getModel('accolade_button/button');

                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('accolade_button')->__('The association was successfully deleted'));
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
        $model  = Mage::getModel('accolade_button/button')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('button_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('accolade_button/button');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Button Association Manager'), Mage::helper('adminhtml')->__('Button Association Manager'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('accolade_button/adminhtml_button_edit'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('accolade_button')->__('The requested association does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function exportButtonCsvAction()
    {
        $fileName = 'accolade_button.csv';
        $grid = $this->getLayout()->createBlock('accolade_button/adminhtml_button_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    public function exportButtonExcelAction()
    {
        $fileName = 'accolade_button.xml';
        $grid = $this->getLayout()->createBlock('accolade_button/adminhtml_button_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('accolade_button/adminhtml_button_grid')->toHtml()
        );
    }

    public function indexAction() {
        $this->loadLayout();
        $this->_title($this->__("Manage Associations"));
        $this->_setActiveMenu("accolade_button/button");
        $this->_addContent($this->getLayout()->createBlock('accolade_button/adminhtml_button'));
        $this->renderLayout();
    }

    public function massDeleteAction() {
        $associationIds = $this->getRequest()->getParam('accolade_button');
        if(!is_array($associationIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Association(s)'));
        } else {
            try {
                foreach ($associationIds as $id) {
                    $association = Mage::getModel('accolade_button/button')->load($id);
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

            $model = Mage::getModel('accolade_button/button');
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

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('accolade_button')->__('The association was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('accolade_button')->__('Unable to save the association'));
        $this->_redirect('*/*/');
    }
}
