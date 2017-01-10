<?php

class Accolade_Bttn_Helper_Api extends Mage_Core_Helper_Abstract
{
    protected $_apiUrl = "https://your.bt.tn/serves/";

    protected $_version = "v1";

    /**
     * Retrieve the callback data necessary for button presses to work
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
     * @return string accolade/bttn/api_key
     */
    public function getApiKey()
    {
        return Mage::getStoreConfig('accolade/bttn/api_key');
    }

    /**
     * Build the API URL unique to the merchant
     *
     * @return string URL
     */
    public function getApiUrl()
    {
        return $this->_apiUrl . $this->getMerchant() . "/" . $this->_version . "/";
    }

    public function getCallbackUrl()
    {
        return Mage::getUrl("accolade_bttn/api/callback");
    }

    /**
     * Send a request to the API
     *
     * @param string $endpoint
     * @param string $method
     * @param array $data
     * @param array $headers
     * @param string $callback
     *
     * @return object|boolean $response
     */
    protected function request($endpoint = "", $scope = 'read', $method = "get", $data = array(), $headers = array())
    {
        $apiHeaders = array(
            "X-Api-Key: " . $this->getApiKey($scope)
        );

        if (count($headers)) {
            $requestHeaders = array_merge($headers, $apiHeaders);
        } else {
            $requestHeaders = $apiHeaders;
        }

        $client = new Varien_Http_Client();
        $client->setUri($this->getApiUrl() . $endpoint);
        $client->setMethod(strtoupper($method));
        $client->setConfig(array(
            'strict' => false
        ));
        $client->setHeaders($requestHeaders);
        $client->setRawData(json_encode(array($data)), 'application/json');
        try {
            $response = $client->request();
        } catch (Exception $e) {
            Mage::log("Error on $endpoint request:", null, 'bttn-error.log', true);
            Mage::log(print_r($e->getMessage(), true), null, 'bttn-error.log', true);
            Mage::log(print_r($client->getLastRequest(), true), null, 'bttn-error.log', true);
            return $e->getMessage();
        }
        return json_decode($response->getBody());
    }

    /**
     * Associate a button with the merchant
     *
     * @param string $buttonId
     * @return array|exception
     */
    public function associateBttn($buttonId)
    {
        $data = array(
            'code' => $buttonId
        );
        $response = $this->request("associate", 'associate', "post", $data);
        if (is_array($response) && count($response) > 0) {
            if (isset($response[0]->associd)) {
                // The association was successful, set the button data and return the association_id
                $dataResponse = $this->setBttnData($response[0]->associd, $this->_getCallbackData());
                return array('association_id' => $response[0]->associd);
            } else if (isset($response[0]->error)) {
                if ($response[0]->error == "already_associated") {
                    return new Exception("Bt.tn already associated");
                }
                return new Exception("Bt.tn association failed");
            }
        }
        return new Exception("Bt.tn association failed");
    }

    /**
     * Retrieve the associations for the merchant
     *
     * @return array $associations
     */
    public function getAssociations()
    {
        $response = $this->request("associate", 'associate');
        Mage::log($response, null, "bttn.log");
    }

    /**
     * Release the button association
     *
     * @param string $associationId
     */
    public function releaseBttn($associationId)
    {
        $data = array(
            'associd' => $associationId
        );
        $response = $this->request("release", 'associate', "post", $data);
        if (is_array($response) && count($response) > 0) {
            if (isset($response[0]->associd)) {
                return array('association_id' => $response[0]->associd);
            } else if (isset($response[0]->error)) {
                if ($response[0]->error == "does_not_exist") {
                    return new Exception("Bt.tn not associated");
                }
                return new Exception("Bt.tn release failed");
            }
        }
        return new Exception("Bt.tn release failed");
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
        $response = $this->request("data/" . $associationId, 'write', "delete");
        Mage::log($response, null, "bttn.log");
    }

    /**
     * Get the data associated with a button
     *
     * @param string $associationId The association ID for the button
     */
    public function getBttnData($associationId)
    {
        $response = $this->request("data/" . $associationId);
        Mage::log($response, null, "bttn.log");
    }

    /**
     * Set data for an associated button
     *
     * @param $associationId
     * @param array $data
     */
    public function setBttnData($associationId, $data = array())
    {
        $requestData = array(
            'associd' => $associationId,
            'data' => $data
        );
        $response = $this->request("data", 'write', "post", $requestData);
        Mage::log($response, null, "bttn.log");
        return $response;
    }

    /**
     * Request generation of a new key
     *
     * @param string $scope The permissions scope to set for the newly generated key
     * @param string $name  The name for the new key
     *
     * @return bool True on success or false on failure, 
     * messages logged to bttn-keys.log
     */
    public function newKey($scope = '', $name = '') 
    {
        $response = $this->request('newkey', 'admin', 'post');
        Mage::log(print_r($response, true), null, 'bttn-keys.log', true);
        if (is_array($response) && count($response) > 0) {
            $key = $response[0];
            if (isset($key->error)) {
                $error = $key->error;
                if (isset($key->reason)) {
                     $error .= ': ' . $key->reason;
                }
                return false;
            } else {
                $keyModel = Mage::getModel('accolade_bttn/key');
                $keyModel->setKey($key->apikey);
                $keyModel->setPrefix($key->prefix);
                $keyModel->setName($key->name);
                $keyModel->setScope($key->scope);
                $keyModel->setCreated($key->created);
                $keyModel->setExpires($key->expires);
                $keyModel->setActive(1);
                if (!empty($scope)) {
                    $update = $this->updateKey($key->prefix, $scope, $name);
                    if ($update) {
                        if (is_array($scope)) {
                            $keyModel->setScope(implode(',', $scope));
                        } else {
                            $keyModel->setScope($scope);
                        }
                        $keyModel->setScope($scope);
                        $keyModel->setName($name);
                    }
                }
                try {
                    $keyModel->save();
                    Mage::log(
                        "New {}$scope} key with id: {$keyModel->getId()}", 
                        null, 
                        'bttn-keys.log', 
                        true
                    );
                    return true;
                } catch (Exception $e) {
                    Mage::log(
                        "Failed to create new key: {$e->message}", 
                        null, 
                        'bttn-keys.log', 
                        true
                    );
                    return false;
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
            return "Prefix must be set to update keys";
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
