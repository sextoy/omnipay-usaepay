<?php

namespace Omnipay\USAePay\Message;

use Omnipay\Tests\TestCase;

class VoidRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new VoidRequest($this->getHttpClient(), $this->getHttpRequest());

        $this->request->initialize(array(
            'amount' => '10.00',
            'transactionReference' => '108889911',
        ));
    }

    public function testGetData()
    {
        $data = $this->request->getData();

        $this->assertSame('10.00', $data['amount']);
    }

    /**
     * @group mock
     */
    public function testMockSendSuccess()
    {
        $this->setMockHttpResponse('VoidSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertStringMatchesFormat('%d', $response->getTransactionReference());
        $this->assertSame('TESTMD', $response->getAuthorizationCode());
        $this->assertEmpty('', $response->getMessage());
    }

    /**
     * @group network
     *
     * @depends Omnipay\USAePay\Message\PurchaseRequestTest::testSendSuccess
     */
    public function testSendSuccess($data)
    {
        $this->request->setSandbox(true);
        $this->request->setTestMode(true);
        $this->request->setSource('_7M6zPa7P9k19g82M1aR8aOPvgFVcIWv');
        $this->request->setPin('123456');
        $this->request->setInvoice(substr(md5(rand()), 0, 10));
        $this->request->setTransactionReference($data['transactionReference']);
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertStringMatchesFormat('%d', $response->getTransactionReference());
        $this->assertEmpty('', $response->getMessage());
    }

    /**
     * @group mock
     */
    public function testMockSendFailure()
    {
        $this->setMockHttpResponse('VoidFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('', $response->getTransactionReference());
        $this->assertSame('Unable to locate transaction', $response->getMessage());
    }

    /**
     * @group network
     */
    public function testSendFailure()
    {
        $this->request->setSandbox(true);
        $this->request->setTestMode(true);
        $this->request->setSource('_7M6zPa7P9k19g82M1aR8aOPvgFVcIWv');
        $this->request->setPin('123456');
        $this->request->setInvoice(substr(md5(rand()), 0, 10));
        $this->request->setTransactionReference('100000000');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('', $response->getTransactionReference());
        $this->assertSame('Unable to locate transaction', $response->getMessage());
    }
}
