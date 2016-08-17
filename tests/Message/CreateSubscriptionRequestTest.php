<?php

namespace Omnipay\USAePay\Message;

use Omnipay\Tests\TestCase;

class CreateSubscriptionRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new CreateSubscriptionRequest($this->getHttpClient(), $this->getHttpRequest());

        $this->request->initialize(array(
            'amount' => '1.00',
            'card' => $this->getValidCard(),
            'addCustomer' => true,
            'interval' => 'month',
            'intervalCount' => 3,
            'description' => 'Test recurring',
        ));
    }

    public function testGetData()
    {
        $data = $this->request->getData();

        $this->assertSame('1.00', $data['amount']);
        $this->assertSame('monthly', $data['interval']);
        $this->assertSame(3, $data['intervalCount']);
    }

    /**
     * @group mock
     */
    public function testMockSendSuccess()
    {
        $this->setMockHttpResponse('CreateSubscriptionSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertStringMatchesFormat('%d', $response->getTransactionReference());
        $this->assertStringMatchesFormat('%d', $response->getAuthorizationCode());
        $this->assertStringMatchesFormat('%d', $response->getCustomerReference());
    }

    /**
     * @group network
     */
    public function testSendSuccess()
    {
        $this->request->setSandbox(true);
        $this->request->setTestMode(true);
        $this->request->setSource('_7M6zPa7P9k19g82M1aR8aOPvgFVcIWv');
        $this->request->setPin('123456');
        $this->request->setInvoice(substr(md5(rand()), 0, 10));
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertStringMatchesFormat('%d', $response->getTransactionReference());
        $this->assertStringMatchesFormat('%d', $response->getAuthorizationCode());
        $this->assertStringMatchesFormat('%d', $response->getCustomerReference());

        return [
            'transactionReference' => $response->getTransactionReference()
        ];
    }

    /**
     * @group mock
     */
    public function testMockSendFailure()
    {
        $this->setMockHttpResponse('CreateSubscriptionFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertStringMatchesFormat('%d', $response->getTransactionReference());
        $this->assertSame('Do not Honor', $response->getMessage());
    }

    /**
     * @group network
     */
    public function testSendFailure()
    {
        $card = $this->getValidCard();
        $card['number'] = '4000300211112228';
        $card['expiryMonth'] = '09';
        $card['expiryYear'] = '2019';
        $card['cvv'] = '999';

        $this->request->setCard($card);
        $this->request->setSandbox(true);
        $this->request->setTestMode(true);
        $this->request->setSource('_7M6zPa7P9k19g82M1aR8aOPvgFVcIWv');
        $this->request->setPin('123456');
        $this->request->setInvoice(substr(md5(rand()), 0, 10));
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertStringMatchesFormat('%d', $response->getTransactionReference());
        $this->assertStringMatchesFormat('%d', $response->getAuthorizationCode());
    }
}
