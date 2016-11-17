<?php
class Accolade_Bttn_CustomerController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
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

    public function editAction()
    {
        $error = 0;
        $params = $this->getRequest()->getParams();
        // Make sure the paramter we want is set
        if (isset($params['id'])) {
            // Make sure we can get the bttn
            if ($bttn = Mage::getModel('accolade_bttn/bttn')->getBttnById($params['id'])) {
                // Make sure the button belongs to the customer trying to access it
                if ($customer = Mage::getSingleton('customer/session')) {
                    if ($customer->getId() == $bttn->getCustomerId()) {
                        $customer->setBttn($bttn);
                    } else {
                        $error = 1;
                        Mage::getSingleton('core/session')->addError($this->__('That bttn is not associated with your account.'));
                    }
                } else {
                    $error = 1;
                    Mage::getSingleton('core/session')->addError($this->__('Unable to retrieve session data.'));
                }
            } else {
                $error = 1;
                Mage::getSingleton('core/session')->addError($this->__('Unable to retrieve bttn data.'));
            }
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
        $entityId = Mage::getSingleton('accolade_bttn/bttn')->getBttnEntityId();
        $checkShippingAddress = Mage::getModel('accolade_bttn/bttn')->getCheckShippingAddress();
        if (!$checkShippingAddress) {
            $error = 1;
            Mage::getSingleton('core/session')->addError('Please add your address from the account settings!');
        }
        $apiHelper = Mage::helper('accolade_bttn/api');
        if ($entityId) {
            $model = Mage::getModel('accolade_bttn/bttn')->load($entityId);
        } else {
            $model = Mage::getSingleton('accolade_bttn/bttn');
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
                if ($entityId) {
                    $model->addData($data);
                } else {
                    $data['customer_id'] = Mage::getModel('accolade_bttn/bttn')->getCustomerId();
                    $model->setData($data);
                }
                try {
                    if ($entityId) {
                        $model->setId($entityId)->save();
                    } else {
                        $model->save();
                    }
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
        return $this->_redirectReferer();
    }
}
