<?php
class Accolade_Bttn_CustomerController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        parent::preDispatch();
        $loginUrl = Mage::helper('customer')->getLoginUrl();
        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function deleteAction()
    {
        $error = 0;
        $params = $this->getRequest()->getParams();
        $customer = Mage::getSingleton('customer/session');
        if (isset($params['id'])) {
            $error = Mage::getModel('accolade_bttn/bttn')->setCustomerBttn($customer, $params['id']);
        }
        if ($error == 0) {
            $bttn = $customer->getBttn();
            $response = Mage::helper('accolade_bttn/api')->releaseBttn($bttn->getAssociationId());
            if (gettype($response) == 'array') {
                $bttn->delete();
                Mage::getSingleton('core/session')->addSuccess(Mage::helper('core')->quoteEscape($this->__('Bt.tn successfully deleted')));
            } else {
                if (gettype($response) == 'object' && get_class($response) == 'Exception') {
                    Mage::getSingleton('core/session')->addError($this->__($response->getMessage()));
                } else {
                    Mage::getSingleton('core/session')->addError($this->__('Bt.tn API Error'));
                }
            }
        }
        $this->_forward('index');
    }

    public function editAction()
    {
        $error = 0;
        $params = $this->getRequest()->getParams();
        // Make sure the paramter we want is set
        if (isset($params['id'])) {
            $error = Mage::getModel('accolade_bttn/bttn')->setCustomerBttn(Mage::getSingleton('customer/session'), $params['id']);
        }
        if ($error == 1) {
            $this->_forward('index');
        } else {
            $this->loadLayout();
            $this->renderLayout();
        }
    }

    public function newAction()
    {
        Mage::getSingleton('customer/session')->setBttn(Mage::getModel('accolade_bttn/bttn'));
        $this->_forward('edit');
    }

    public function saveAction()
    {
        $error = 0;
        $checkShippingAddress = Mage::getModel('accolade_bttn/bttn')->getCheckShippingAddress();
        if (!$checkShippingAddress) {
            $error = 1;
            Mage::getSingleton('core/session')->addError('Please add your address from the account settings!');
        }
        $apiHelper = Mage::helper('accolade_bttn/api');
        $model = Mage::getSingleton('customer/session')->getBttn();
        $new = false;
        if (!$model) {
            $model = Mage::getSingleton('accolade_bttn/bttn');
            $new = true;
        }
        if (empty($model)) {
            Mage::getSingleton('core/session')->addError($this->__('Unable to retrieve Bt.tn data'));
        } else {
            $values = array(
                "button_id" => array(
                    "test" => function($id) {
                        Mage::log('New ID: ' . $id, null, 'bttn.log');
                        return preg_match('/[0-9]{4}\-[0-9]{4}\-[0-9]{4}\-[0-9]{4}/', $id) === 1;
                    },
                    "error_message" => $this->__('Invalid Bt.tn device ID!'),
                    "api_call" => function($value) use ($apiHelper) {
                        return $apiHelper->associateBttn($value);
                    }
                ),
                "shipping_method" => array(
                    "test" => function($method) {
                        return !empty($method);
                    },
                    "error_message" => $this->__('Invalid shipping method')
                ),
                "payment_method" => array(
                    "test" => function($method) {
                        return !empty($method);
                    },
                    "error_message" => $this->__('Invalid payment method')
                ),
                "order_method" => array(
                    "test" => function($method) {
                        return !empty($method);
                    },
                    "error_message" => $this->__('Invalid order method')
                ),
            );
            $data = array();
            foreach ($values as $key => $validation) {
                if (isset($_POST[$key])) {
                    $value = $_POST[$key];
                    $update = true;
                    if ($model->getData($key) == $value) {
                        $update = false;
                    }
                    if ($update) {
                        $valid = true;
                        if (is_array($validation) && isset($validation['test'])) {
                            $valid = call_user_func($validation['test'], $value);
                        }
                        if (is_array($validation) && isset($validation['api_call'])) {
                            $response = call_user_func($validation['api_call'], $value);
                            if (gettype($response) == 'array') {
                                $data = array_merge($data, $response);
                            } else {
                                $error = 1;
                                if (gettype($response) == 'object' && get_class($response) == 'Exception') {
                                    Mage::getSingleton('core/session')->addError($this->__($response->getMessage()));
                                } else {
                                    Mage::getSingleton('core/session')->addError($this->__('Bt.tn API Error'));
                                }
                            }
                        }
                        if ($valid) {
                            $data[$key] = $value;
                        } else {
                            $error = 1;
                            Mage::getSingleton('core/session')->addError($validation['error_message']);
                        }
                    }
                }
            }
            if (count($data) > 0) {
                if ($new) {
                    $model->addData($data);
                } else {
                    $data['customer_id'] = Mage::getModel('accolade_bttn/bttn')->getCustomerId();
                    $model->setData($data);
                }
                try {
                    $model->save();
                } catch (Exception $e){
                    $error = 1;
                    Mage::log($e->getMessage());
                }
                if ($error == 0) {
                    Mage::getSingleton('core/session')->addSuccess('Bt.tn settings successfully updated!');
                }
            }
        }
        Mage::getSingleton('customer/session')->setBttn(Mage::getModel('accolade_bttn/bttn'));
        return $this->_forward('index');
    }
}
