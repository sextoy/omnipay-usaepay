<?php

namespace Omnipay\USAePay\Message;

use Omnipay\Tests\TestCase;

class CreateCardRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new CreateCardRequest($this->getHttpClient(), $this->getHttpRequest());

        $this->request->initialize(array(
            'amount' => '1.00',
            'card' => $this->getValidCard(),
        ));
    }

    public function testGetData()
    {
        $data = $this->request->getData();

        $this->assertSame('1.00', $data['amount']);
    }

    /**
     * @group mock
     */
    public function testMockSendSuccess()
    {
        $this->setMockHttpResponse('CreateCardSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('', $response->getTransactionReference());
        $this->assertSame('', $response->getAuthorizationCode());
        $this->assertSame('', $response->getMessage());
        $this->assertStringMatchesFormat('%s-%s-%s-%s', $response->getCardReferenceToken());
        $this->assertStringMatchesFormat('XXXXXXXXXXXX%d', $response->getMaskedCardNumber());
        $this->assertSame('Visa', $response->getCardType());
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
        $this->assertSame('', $response->getTransactionReference());
        $this->assertSame('', $response->getAuthorizationCode());
        $this->assertSame('', $response->getMessage());
        $this->assertStringMatchesFormat('%s-%s-%s-%s', $response->getCardReferenceToken());
        $this->assertStringMatchesFormat('XXXXXXXXXXXX%d', $response->getMaskedCardNumber());
        $this->assertSame('Visa', $response->getCardType());

        $this->request = new AuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(array(
            'amount' => '1.00',
            'currency' => 'USD',
        ));
        $this->request->setCardReference($response->getCardReferenceToken());
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
        $this->markTestIncomplete(
            'Need to find a way to make cc:save fail.'
        );

        $this->setMockHttpResponse('CreateCardFailure.txt');
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
        $this->assertTrue($response->isSuccessful());

        $this->request = new AuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(array(
            'amount' => '1.00',
            'currency' => 'USD',
        ));
        $this->request->setCardReference($response->getCardReferenceToken());
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
        $this->assertSame('Do not Honor', $response->getMessage());
    }
}
