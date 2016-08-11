<?php

namespace Omnipay\USAePay\Message;

use Omnipay\Tests\TestCase;

class PurchaseRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

        $this->request->initialize(array(
            'amount' => '10.00',
            'currency' => 'USD',
            'card' => $this->getValidCard(),
        ));
    }

    public function testGetData()
    {
        $data = $this->request->getData();

        $this->assertSame('10.00', $data['amount']);
        $this->assertSame('usd', $data['currency']);
    }

    /**
     * @group mock
     */
    public function testMockSendSuccess()
    {
        $this->setMockHttpResponse('PurchaseSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('0', $response->getTransactionReference());
        $this->assertSame('TESTMD', $response->getAuthorizationCode());
        $this->assertSame('', $response->getMessage());
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
        $this->assertSame('Approved', $response->getMessage());

        return [
            'transactionReference' => $response->getTransactionReference()
        ];
    }

    /**
     * @group mock
     */
    public function testMockSendFailure()
    {
        $this->setMockHttpResponse('PurchaseFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertStringMatchesFormat('%d', $response->getTransactionReference());
        $this->assertSame('Card Declined (00)', $response->getMessage());
    }

    /**
     * @group network
     */
    public function testSendFailure()
    {
        $card = $this->getValidCard();
        $card['number'] = '4000300011112220';
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
        $this->assertSame('Card Declined (00)', $response->getMessage());
    }
}
