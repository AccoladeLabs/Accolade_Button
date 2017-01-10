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
 * The helper class for the API functions. Allows for easy access to all of the
 * Bt.tn API endpoints
 *
 * @category Magento
 * @package  Accolade_Bttn
 * @author   Accolade Partners <info@accolade.fi>
 * @license  OSL-3.0 https://opensource.org/licenses/OSL-3.0 
 * @link     https://accolade.fi
 */

class Accolade_Bttn_Helper_Api extends Mage_Core_Helper_Abstract
{
    protected $_apiUrl = 'https://your.bt.tn/serves/';

    protected $_version = 'v1';

    /**
     * Retrieve the callback data necessary for button presses to work
     *
     * @param string $button_id      The button's association IDd
     * @param string $association_id The button's association ID
     *
     * @return array
     */
    protected function _getCallbackData($button_id, $association_id) {
        return array(
            'pressed' => array(
                'http' => array(
                    'url'       => $this->getCallbackUrl(),
                    'method'    => 'post',
                    'headers'   => array(
                        'X-Api-Key' => $this->getApiKey()
                    ),
                    'json'      => array(
                        'association' => $association_id,
                        'button' => $button_id,
                        'type' => 'short'
                    )
                )
            ),
            'pressed-long' => array(
                'http' => array(
                    'url'       => $this->getCallbackUrl(),
                    'method'    => 'post',
                    'headers'   => array(
                        'X-Api-Key' => $this->getApiKey()
                    ),
                    'json'      => array(
                        'association' => $association_id,
                        'button' => $button_id,
                        'type' => 'long'
                    )
                )
            )
        );
    }

    /**
     * Retrieve the merchant name from configuration
     *
     * @return string accolade/bttn/merchant_name
     */
    public function getMerchant()
    {
        return Mage::getStoreConfig('accolade/bttn/merchant_name');
    }

    /**
     * Retrieve the API key from configuration
     *
     * @param string $scope The permissions level required for the action requested
     *
     * @return string API key for the requested scope
     */
    public function getApiKey($scope = 'read')
    {
        switch ($scope) {
        case 'admin':
            return Mage::getStoreConfig('accolade/bttn/api_key');
        case 'associate':
            // Fallthrough
        case 'press':
            // Fallthrough
        case 'read':
            // Fallthrough
        case 'write':
            return Mage::getModel('accolade_bttn/key')->getKeyByScope($scope);
        default: 
            throw new Exception('Undefined API key scope: $scope');
        }
    }

    /**
     * Build the API URL unique to the merchant
     *
     * @return string URL
     */
    public function getApiUrl()
    {
        return $this->_apiUrl . $this->getMerchant() . '/' . $this->_version . '/';
    }

    /**
     * Retrieve the callback URL for the store
     *
     * @return string $callbackUrl
     */
    public function getCallbackUrl()
    {
        return Mage::getUrl('accolade_bttn/api/callback');
    }

    /**
     * Send a request to the API
     *
     * @param string $endpoint The endpoint to send the API request to
     * @param string $scope    The scope of the API key needed to make the 
     * request
     * @param string $method   The HTTP Method needed to complete the
     * transaction
     * @param array  $data     The data to send in the request
     * @param array  $headers  Any additional headers to send in the request
     *
     * @return object|boolean $response
     */
    protected function request(
        $endpoint = '', 
        $scope = 'read', 
        $method = 'get', 
        $data = array(), 
        $headers = array()
    ) {
        $apiHeaders = array(
            'X-Api-Key: ' . $this->getApiKey($scope)
        );
        if (count($headers)) {
            $requestHeaders = array_merge($headers, $apiHeaders);
        } else {
            $requestHeaders = $apiHeaders;
        }

        $client = new Varien_Http_Client();
        $client->setUri($this->getApiUrl() . $endpoint);
        $client->setMethod(strtoupper($method));
        $client->setConfig(
            array(
                'strict' => false
            )
        );
        $client->setHeaders($requestHeaders);
        $client->setRawData(json_encode(array($data)), 'application/json');
        try {
            $response = $client->request();
            if ($response->getStatus() == 200) {
                return json_decode($response->getBody());
            } else {
                return $response->getStatus() . ': ' . $response->getMessage();
            }
        } catch (Exception $e) {
            Mage::log('Error on $endpoint request:', null, 'bttn-error.log', true);
            Mage::log(print_r($e->getMessage(), true), null, 'bttn-error.log', true);
            Mage::log(
                print_r($client->getLastRequest(), true), 
                null, 
                'bttn-error.log', 
                true
            );
            return $e->getMessage();
        }   
    }

    /**
     * Associate a button with the merchant
     *
     * @param string $buttonId The Id of the button to be associated
     *
     * @return array|exception
     */
    public function associateBttn($buttonId)
    {
        $data = array(
            'code' => $buttonId
        );
        $response = $this->request('associate', 'associate', 'post', $data);
        if (is_array($response) && count($response) > 0) {
            if (isset($response[0]->associd)) {
                // The association was successful, set the button data and 
                // return the association_id
                $dataResponse = $this->setBttnData(
                    $response[0]->associd, 
                    $this->_getCallbackData()
                );
                return array('association_id' => $response[0]->associd);
            } else if (isset($response[0]->error)) {
                if ($response[0]->error == 'already_associated') {
                    return new Exception('Bt.tn already associated');
                }
                return new Exception('Bt.tn association failed');
            }
        }
        return new Exception('Bt.tn association failed');
    }

    /**
     * Retrieve the associations for the merchant
     *
     * @return array $associations
     */
    public function getAssociations()
    {
        $response = $this->request('associate', 'associate');
        Mage::log($response, null, 'bttn.log');
    }

    /**
     * Release the button association
     *
     * @param string $associationId The association ID of the button to be released
     *
     * @return mixed Association ID on success and an Exception on failure
     */
    public function releaseBttn($associationId)
    {
        $data = array(
            'associd' => $associationId
        );
        $response = $this->request('release', 'associate', 'post', $data);
        if (is_array($response) && count($response) > 0) {
            if (isset($response[0]->associd)) {
                return array('association_id' => $response[0]->associd);
            } else if (isset($response[0]->error)) {
                if ($response[0]->error == 'does_not_exist') {
                    return new Exception('Bt.tn not associated');
                }
                return new Exception('Bt.tn release failed');
            }
        }
        return new Exception('Bt.tn release failed');
    }

    /**
     * Clear the data associated with a button
     *
     * @param string $associationId The association ID for the button
     *
     * @return null
     */
    public function clearBttnData($associationId)
    {
        $response = $this->request('data/' . $associationId, 'write', 'delete');
        Mage::log($response, null, 'bttn.log');
    }

    /**
     * Get the data associated with a button
     *
     * @param string $associationId The association ID for the button
     *
     * @return mixed The data attached to the button or an Exception on error
     */
    public function getBttnData($associationId)
    {
        $response = $this->request('data/' . $associationId);
        Mage::log($response, null, 'bttn.log');
        return $response;
    }

    /**
     * Set data for an associated button
     *
     * @param string $associationId The association ID of the button
     * @param array  $data          The data to be attached to the button
     *
     * @return mixed The result of the request
     */
    public function setBttnData($associationId, $data = array())
    {
        $requestData = array(
            'associd' => $associationId,
            'data' => $data
        );
        $response = $this->request('data', 'write', 'post', $requestData);
        Mage::log($response, null, 'bttn.log');
        return $response;
    }

    /**
     * Request generation of a new key
     *
     * @param string $scope The permissions scope to set for the newly generated key
     * @param string $name  The name for the new key
     *
     * @return string Message of success or failure
     */
    public function newKey($scope = '', $name = '') 
    {
        $key = $this->request('admin/newkey', 'admin', 'post');
        if (is_object($key) && count($key) > 0) {
            if (isset($key->error)) {
                $error = $key->error;
                if (isset($key->reason)) {
                     $error .= ': ' . $key->reason;
                }
                Mage::log('ERROR: ' . $error, null, 'bttn-error.log', true);
                return $error;
            } else {
                $data = array(
                    'key'     => $key->apikey,
                    'prefix'  => $key->prefix,
                    'name'    => $key->name,
                    'scope'   => implode(',', $key->scope),
                    'created' => $key->created,
                    'expires' => $key->expires,
                    'active'  => 1
                );
                if (!empty($scope)) {
                    $update = $this->updateKey($key->prefix, $scope, $name);
                    if ($update) {
                        if (is_array($scope)) {
                            $data['scope'] = (implode(',', $scope));
                        } else {
                            $data['scope'] = $scope;
                        }
                        $data['name'] = $name;
                    }
                }
                try {
                    $keyModel = Mage::getModel('accolade_bttn/key');
                    $keyModel->addData($data);
                    $keyModel->save();
                    return 'New ' . $scope . ' key with id: ' . $keyModel->getId();
                } catch (Exception $e) {
                    return 'Failed to create new key: ' . $e->getMessage();
                }
            }
        }
    }

    /**
     * Update API key data on Bt.tn Servers. Returns true on success or false on 
     * failure.
     *
     * @param string $prefix The prefix of the key you would like to update
     * @param string $scope  The new scope for the key
     * @param string $name   The new name for the key
     *
     * @return bool   $result True on success or false on failure
     * */
    public function updateKey($prefix, $scope = '', $name = '') 
    {
        if (empty($prefix)) {
            return 'Prefix must be set to update keys';
        }
        $requestData = array(
            'prefix' => $prefix
        );
        if (isset($scope)) {
            if (is_array($scope)) {
                $requestData['scope'] = $scope;
            } else {
                $requestData['scope'] = array (
                    $scope
                );
            }
        }
        $response = $this->request('admin/keys', 'admin', 'post', $requestData);
        $result = $response[0];
        if (isset($result->error)) {
            $error = $result->error;
            if (isset($result->reason)) {
                 $error .= ': ' . $result->reason;
            }
            Mage::log('Error updating key: ' . $error, null, 'bttn-error.log');
            return false;
        } else {
            return true;
        }
    }
}
