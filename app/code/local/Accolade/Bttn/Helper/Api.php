<?php
/**
 * Created by PhpStorm.
 * User: Billy
 * Date: 8/25/2016
 * Time: 2:22 PM
 */

class Accolade_Bttn_Helper_Api extends Mage_Core_Helper_Abstract
{
    private $apiUrl = "https://your.bt.tn/serves/";

    private $version = "v1";

    /**
     * Retrieve the merchant name from configuration
     *
     * @return accolade/bttn/merchant_name
     */
    public function getMerchant()
    {
        return Mage::getConfig('accolade/bttn/merchant_name');
    }

    /**
     * Retrieve the API key from configuration
     *
     * @return accolade/bttn/api_key
     */
    public function getApiKey()
    {
        return Mage::getConfig('accolade/bttn/api_key');
    }

    /**
     * Build the API URL unique to the merchant
     *
     * @return accolade/bttn/merchant_name
     */
    public function getApiUrl()
    {
        return $this->apiUrl . $this->getMerchant() . "/" . $this->version . "/";
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
     * @return object $response
     */
    protected function request( $endpoint = "", $method = "get", $data = array(), $headers = array())
    {
        $apiHeaders = array(
            "X-Api-Key" => $this->getApiKey()
        );

        if (count($headers)) {
            $requestHeaders = array_merge($headers, $apiHeaders);
        } else {
            $requestHeaders = $apiHeaders;
        }

        $curlOptions = array(
            CURLOPT_HTTPHEADER => $requestHeaders,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $this->getApiUrl() . $endpoint
        );

        switch ($method) {
            case "delete":
                array_push(
                    $curlOptions,
                    array(
                        CURLOPT_CUSTOMREQUEST => "DELETE"
                    )
                );
                break;
            case "post":
                array_push(
                    $curlOptions,
                    array(
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => $data
                    )
                );
                break;
        }

        $ch = curl_init();

        curl_setopt_array(
            $ch,
            $curlOptions
        );

        return curl_exec($ch);
    }

    /**
     * Associate a button with the merchant
     *
     * @param string $buttonId
     */
    public function associateBttn($buttonId)
    {
        $data = array(
            'code' => $buttonId
         );
        $response = $this->request("associate", "post", $data);
        Mage::log($response, null, "bttn.log");
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
            'associd' => $associationId
        );
        $response = $this->request("release", "post", $data);
        Mage::log($response, null, "bttn.log");
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
    }
}