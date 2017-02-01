<?php
/**
 * This file is part of the Accolade Button for Commerce Magento module.
 * Please see the license in the root of the directory or at the link below.
 *
 * PHP Version 5.6
 *
 * @category Magento
 * @package  Accolade_Bttn
 * @author   Accolade Partners <info@accolade.fi>
 * @license  OSL-3.0 https://opensource.org/licenses/OSL-3.0 
 * @link     https://accolade.fi
 */

/**
 * The customer controller handles all of requests to create, edit, and delete
 * customer button associations and related settings
 *
 * @category Magento
 * @package  Accolade_Bttn
 * @author   Accolade Partners <info@accolade.fi>
 * @license  OSL-3.0 https://opensource.org/licenses/OSL-3.0 
 * @link     https://accolade.fi
 */
class Accolade_Bttn_CustomerController extends Mage_Core_Controller_Front_Action
{
    /**
     * Authenticate the user
     *
     * @return null
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $loginUrl = Mage::helper('customer')->getLoginUrl();
        if (!Mage::getSingleton('customer/session')->authenticate(
            $this, 
            $loginUrl
        )
        ) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    /**
     * The index page where the list of associated buttons is displayed to the
     * customer
     *
     * @return null
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * The endpoint to handle deleting the button association and its related
     * settings.
     *
     * @return null
     */
    public function deleteAction()
    {
        $error = 0;
        $params = $this->getRequest()->getParams();
        $customer = Mage::getSingleton('customer/session');
        if (isset($params['id'])) {
            $error = Mage::getModel('accolade_bttn/bttn')
                ->setCustomerBttn($customer, $params['id']);
        }
        if ($error == 0) {
            $bttn = $customer->getBttn();
            $response = Mage::helper('accolade_bttn/api')
                ->releaseBttn($bttn->getAssociationId());
            if (gettype($response) == 'array') {
                $bttn->delete();
                Mage::getSingleton('core/session')
                    ->addSuccess(
                        Mage::helper('core')
                        ->quoteEscape($this->__('Bt.tn successfully deleted'))
                    );
            } else {
                if (gettype($response) == 'object' 
                    && get_class($response) == 'Exception'
                ) {
                    Mage::getSingleton('core/session')
                        ->addError($this->__($response->getMessage()));
                } else {
                    Mage::getSingleton('core/session')
                        ->addError($this->__('Bt.tn API Error'));
                }
            }
        }
        $this->_forward('index');
    }

    /**
     * The endpoint to handle editing button association data and related
     * settings
     *
     * @return null
     */
    public function editAction()
    {
        $error = 0;
        $params = $this->getRequest()->getParams();
        // Make sure the paramter we want is set
        if (isset($params['id'])) {
            $error = Mage::getModel('accolade_bttn/bttn')
                ->setCustomerBttn(
                    Mage::getSingleton('customer/session'), 
                    $params['id']
                );
        }
        if ($error == 1) {
            $this->_forward('index');
        } else {
            $this->loadLayout();
            $this->renderLayout();
        }
    }

    /**
     * The endpoint to handle the creation of button association data and
     * related settings
     *
     * @return null
     */
    public function newAction()
    {
        $newButton = Mage::getModel('accolade_bttn/bttn');
        Mage::getSingleton('customer/session')
            ->setBttn($newButton);
        $this->_forward('edit');
    }

    /**
     * The endpoint to commit the changes made to the button association
     * data and related settings to the database.
     *
     * @return null
     */
    public function saveAction()
    {
        $error = 0;
        $checkShippingAddress = Mage::getModel('accolade_bttn/bttn')
            ->getCheckShippingAddress();
        if (!$checkShippingAddress) {
            $error = 1;
            Mage::getSingleton('core/session')
                ->addError('Please add your address from the account settings!');
        }
        $apiHelper = Mage::helper('accolade_bttn/api');
        $model = Mage::getSingleton('customer/session')->getBttn();
        $new = false;
        $entityId = $model->getEntityId();
        if (empty($entityId)) {
            $model = Mage::getModel('accolade_bttn/bttn');
            $new = true;
        } else {
        }
        if (empty($model)) {
            Mage::getSingleton('core/session')
                ->addError($this->__('Unable to retrieve Bt.tn data'));
        } else {
            $values = array(
                "button_id" => array(
                    "test" => function ($id) {
                        return preg_match(
                            '/[0-9]{4}\-[0-9]{4}\-[0-9]{4}\-[0-9]{4}/', 
                            $id
                        ) === 1;
                    },
                    "error_message" => $this->__('Invalid Bt.tn device ID!'),
                    "api_call" => function ($value) use ($apiHelper) {
                        return $apiHelper->associateBttn($value);
                    }
                ),
                "shipping_method" => array(
                    "test" => function ($method, $new = false) {
                        if ($new) {
                            return true;
                        } else {
                            return !empty($method);
                        }
                    },
                    "error_message" => $this->__('Invalid shipping method')
                ),
                "payment_method" => array(
                    "test" => function ($method) {
                        return !empty($method);
                    },
                    "error_message" => $this->__('Invalid payment method')
                ),
                "order_method" => array(
                    "test" => function ($method) {
                        return !empty($method);
                    },
                    "error_message" => $this->__('Invalid order method')
                ),
            );
            $data = array();
            $post = $this->getRequest()->getPost();
            foreach ($values as $key => $validation) {
                if (isset($post[$key])) {
                    // Check to see if the values have been updated, and only
                    // update those values which are new
                    $value = $post[$key];
                    $update = true;
                    if ($model->getData($key) == $value) {
                        $update = false;
                    }
                    if ($update) {
                        $valid = true;
                        if (is_array($validation) && isset($validation['test'])) {
                            $valid = call_user_func($validation['test'], $value);
                        }
                        if (is_array($validation) 
                            && isset($validation['api_call'])
                        ) {
                            $response = call_user_func(
                                $validation['api_call'], 
                                $value
                            );
                            if (gettype($response) == 'array') {
                                $data = array_merge($data, $response);
                            } else {
                                $error = 1;
                                if (gettype($response) == 'object' 
                                    && get_class($response) == 'Exception'
                                ) {
                                    Mage::getSingleton('core/session')
                                        ->addError(
                                            $this->__($response->getMessage())
                                        );
                                } else {
                                    Mage::getSingleton('core/session')
                                        ->addError($this->__('Bt.tn API Error'));
                                }
                            }
                        }
                        if ($valid) {
                            $data[$key] = $value;
                        } else {
                            $error = 1;
                            Mage::getSingleton('core/session')
                                ->addError($validation['error_message']);
                        }
                    }
                }
            }
            if (count($data) > 0) {
                try {
                    $data['customer_id'] = Mage::getModel('accolade_bttn/bttn')
                        ->getCustomerId();
                    $model->addData($data);
                    $model->save();
                } catch (Exception $e){
                    $error = 1;
                    Mage::log($e->getMessage(), null, 'bttn-error.log', true);
                }
                if ($error == 0) {
                    Mage::getSingleton('core/session')
                        ->addSuccess('Bt.tn settings successfully updated!');
                }
            }
        }
        Mage::getSingleton('customer/session')
            ->setBttn(Mage::getModel('accolade_bttn/bttn'));
        return $this->_forward('index');
    }
}
