<?php

class Accolade_Button_Helper_ApiTest extends PHPUnit_Framework_TestCase
{
    protected $helper;
    protected $dummyKeys;

    public function setUp()
    {
        Mage::app();
        $this->helper = Mage::helper('accolade_button/api');

        // Prepare some dummy models for use in testing, will be destroyed later
        $this->dummyKeys = array();
        $keyScopes = array(
            'admin',
            'associate',
            'press',
            'read',
            'write'
        );
        foreach ($keyScopes as $scope) {
            $model = Mage::getModel('accolade_button/key')
                ->addData(array(
                    'active' => true,
                    'name' => $scope,
                    'api_key' => $scope . '-key',
                    'prefix' => $scope,
                    'scope' => $scope
                ))
                ->save();
            $this->dummyKeys[$scope] = $model->getId();
        }
    }

    public function testGetMerchant()
    {
        Mage::getConfig()->saveConfig('accolade/button/merchant_name', 'test-merchant')->reinit();
        $this->assertEquals('test-merchant', $this->helper->getMerchant(), 'Merchant name');
    }

    public function testGetApiKey()
    {
        Mage::getConfig()->saveConfig('accolade/button/api_key', 'admin-key')->reinit();
        foreach ($this->dummyKeys as $scope => $id) {
            $this->assertEquals($scope . '-key', $this->helper->getApiKey($scope, $id), "Get " . $scope . "key");
        }
    }

    public function testGetApiUrl()
    {
        // First, set the store config so we don't have any surprises
        Mage::getConfig()->saveConfig('accolade/button/merchant_name', 'test-merchant')->reinit();
        // This is what we're expecting to get:
        $expected = 'https://your.bt.tn/serves/test-merchant/v1/';
        // So let's test!
        $this->assertEquals($expected, $this->helper->getApiUrl(), 'API URL');
    }

    public function testGetCallbackUrl()
    {
        // First, set the store config so we don't have any surprises
        Mage::getConfig()->saveConfig('web/unsecure/base_url', 'https://my-store.loc/')->reinit();
        Mage::getConfig()->saveConfig('web/secure/base_url', 'https://my-store.loc/')->reinit();
        Mage::getConfig()->saveConfig('web/url/use_store', 0)->reinit();
        $expectedUrl = "https://my-store.loc/accolade_button/api/callback/";
        $this->assertEquals($expectedUrl, $this->helper->getCallbackUrl(), "Callback URL");
    }

    public function tearDown()
    {
        foreach ($this->dummyKeys as $key) {
            Mage::getModel('accolade_button/key')
                ->load($key)
                ->delete();
        }
    }
}