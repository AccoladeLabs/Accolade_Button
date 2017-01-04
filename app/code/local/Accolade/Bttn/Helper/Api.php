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
    protected function _getCallbackData() {
        return array(
            'pressed' => array(
                'http' => array(
                    'url'       => $this->getCallbackUrl(),
                    'method'    => 'post',
                    'headers'   => array(
                        'X-Api-Key' => $this->getApiKey()
                    ),
                    'json'      => array(
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
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . "/accolade_bttn/api/callback";
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
    protected function request($endpoint = "", $method = "get", $data = array(), $headers = array())
    {
        $apiHeaders = array(
            "X-Api-Key: " . $this->getApiKey()
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
        $client->setRawData(json_encode($data), 'application/json');
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
            array(
                'code' => $buttonId
            )
        );
        $response = $this->request("associate", "post", $data);
        if (is_array($response) && count($response) > 0) {
            if (isset($response[0]->associd)) {
                // The association was successful, set the button data and return the association_id
                $dataResponse = $this->setBttnData($response[0]->associd, $this->_getCallbackData);
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
        $response = $this->request("associate");
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
            array(
                'associd' => $associationId
            )
        );
        $response = $this->request("release", "post", $data);
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
     * @param $associationId
     */
    public function clearBttnData($associationId)
    {
        $response = $this->request("data/" . $associationId, "delete");
        Mage::log($response, null, "bttn.log");
    }

    /**
     * Get the data associated with a button
     *
     * @param $associationId
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
        $response = $this->request("data", "post", $requestData);
        Mage::log($response, null, "bttn.log");
        return $response;
    }

    /**
     * Post result back to bt.tn for button light feedback
     *
     * @param string $event
     * @param bool $success
     */
    public function sendResult($event, $success = false)
    {
        if ($success) {
            $result = "positive";
        } else {
            $result = "negative";
        }
        $response = $this->request(
            'callback/' . $event, 
            'POST', 
            array(
                array(
                    'result' => $result
                )
            )
        );
        return $reponse;
    }
}
